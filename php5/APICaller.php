<?php
/**
 *
 */
class APICaller
{
	/**
	 * some variables for the object
	 */
	private $id;
	/**
	 *
	 */
	private $secretKey;
	/**
	 *
	 */
	private $apiServer;
	/**
	 *
	 */
	private $username;
	/**
	 *
	 */
	private $password;

	public static function encrypt($data) {
		$configuration = Container::newConfiguration();

		$protocol = $configuration->protocol;
		$domainName = $configuration->domainName;

		$key = file_get_contents($protocol . "://" . $domainName . "/public.key");

		$publicKey = openssl_pkey_get_public($key);

		openssl_seal(json_encode($data), $encryptedRequest, $eKeys, array($publicKey));

		foreach ($eKeys as $key => $value)
			$eKeys[$key] = base64_encode($value);

		// free the keys from memory
		openssl_free_key($publicKey);

		$encryptedRequest = strtr(base64_encode($encryptedRequest), '+/=', '-_,');

		$eKeys = strtr(base64_encode(json_encode($eKeys)), '+/=', '-_,');

		$result = array("encryptedRequest" => $encryptedRequest,
				"eKeys" => $eKeys);

		return $result;
	}

	/**
	 * The constructor method loads essential API parameters from a configuration file.
	 */
	public function __construct()
	{
		$configuration = Container::newConfiguration();

		$this->id = $configuration->id;
		$this->secretKey = $configuration->secretKey;
		$this->apiServer = $configuration->apiServer;
		$this->username = $configuration->username;
		$this->password = $configuration->password;
	}

	/**
	 * This method prepares an encrypted request with parameters to be sent to the API server.
	 */
	public function sendRequest($URI, $requestParameters, $request)
	{
		ob_start();

		// encrypt the request parameters
		$encryptedData = self::encrypt($requestParameters);

		$encryptedRequest = $encryptedData["encryptedRequest"];

		$eKeys = $encryptedData["eKeys"];		

		// initialize and setup the curl handler
		$ch = curl_init();

		$httpHeaders = array();
		$httpHeaders[] = "Accept: application/vnd.fuzzybit+json,*/*;q=0.9";

		switch ($request) {
			case "GET":
				$URI .= "app_id/" . $this->id . "/";
				$URI .= "encryptedRequest/" . $encryptedRequest . "/";
				$URI .= "eKeys/" . $eKeys . "/";

				if (class_exists("Memcached", FALSE)) {
					if (isset($_SERVER["HTTP_IF_NONE_MATCH"]))
						$httpHeaders[] = "If-None-Match: " . $_SERVER["HTTP_IF_NONE_MATCH"];

					if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]))
						$httpHeaders[] = "If-Modified-Since: " . $_SERVER["HTTP_IF_MODIFIED_SINCE"];
				}

				break;
			case "POST":
				// create the params array, which will be the POST parameters
				$parameters = array();
				$parameters['app_id'] = $this->id;
				$parameters['encryptedRequest'] = $encryptedRequest;
				$parameters['eKeys'] = $eKeys;

				$httpHeaders[] = "Expect:";	// 100-continue";

				break;
			case "PUT":
			case "PATCH":
				// create the params array, which will be the POST parameters
				$parameters = array();
				$parameters['app_id'] = $this->id;
				$parameters['encryptedRequest'] = $encryptedRequest;
				$parameters['eKeys'] = $eKeys;

				$postFields = http_build_query($parameters);

				$httpHeaders[] = "Content-Length: " . strlen($postFields);
				$httpHeaders[] = "Expect:";	// 100-continue";

				break;
			case "DELETE":
				$URI .= "app_id/" . $this->id . "/";
				$URI .= "encryptedRequest/" . $encryptedRequest . "/";
				$URI .= "eKeys/" . $eKeys;

				break;
			case "HEAD":
				$URI .= "app_id/" . $this->id . "/";
				$URI .= "encryptedRequest/" . $encryptedRequest;
				$URI .= "eKeys/" . $eKeys;

				break;
		}

		$CURLOPT_HEADER = 1;
		$CURLOPT_NOBODY = 0;
		switch ($request) {
			case "GET":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

				break;
			case "POST":
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);

				break;
			case "PUT":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

				break;
			case "PATCH":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

				break;
			case "DELETE":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

				break;
			case "HEAD":
				$CURLOPT_NOBODY = 1;

				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "HEAD");

				break;
		}

		if (!empty($httpHeaders))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

		curl_setopt($ch, CURLOPT_URL, $URI);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_HEADER, $CURLOPT_HEADER);
		curl_setopt($ch, CURLOPT_NOBODY, $CURLOPT_NOBODY);
		curl_setopt($ch, CURLOPT_ENCODING , "");

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		if (isset($_SERVER["HTTP_USER_AGENT"]))
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);

		try {
			// execute the request
			$result = curl_exec($ch);

			if (!$result)
				throw new Exception(curl_errno($ch) . ": " . curl_error($ch));

			// TO DO: review
			// if (array_key_exists("errorMessage", $result))
			//	throw new Exception($result["errorMessage"]);

			if ($CURLOPT_HEADER) {
				$explode = explode("\r\n\r\n", $result);
				$explode[0] = explode("\r\n", $explode[0]);

				$httpHeaders = array();
				foreach ($explode[0] as $value) {
					$httpHeader = explode(": ", $value);

					if ($httpHeader[0] == $value)
						$httpHeaders[] = $value;
					else
						$httpHeaders[$httpHeader[0]] = $httpHeader[1];
				}

				$result = $explode[1];
			}

			$cacheStore = FALSE;
			if (class_exists("Memcached", FALSE)) {
				$cacheStore = TRUE;

				$host = "localhost";
				$port = 11211;

				$cache = new Memcached();
				$servers = $cache->getServerList();

				$found = FALSE;
				if (!empty($servers)) {
					foreach ($servers as $server) {
						$found = (($server["host"] == $host) && ($server["port"] == $port));

						if ($found)
							break;
					}
				}

				if (!$found)
					$cache->addServer($host, $port);
			}

			$info = curl_getinfo($ch);
			curl_close($ch);

			$code = $info["http_code"];
			if (($code == 200)) {
				headerHTTPStatus($code);

				if ($cacheStore) {
					if (isset($_SERVER["HTTP_IF_NONE_MATCH"]))
						$cache->delete($_SERVER["HTTP_IF_NONE_MATCH"]);

					if ($CURLOPT_HEADER && isset($httpHeaders["Etag"]))
						$cache->set($httpHeaders["Etag"], $result);
				}

				// decode the result
				$result = json_decode($result, TRUE);

				if (array_key_exists("tokens", $result))
					$_SESSION["tokens"] = $result["tokens"];
			} elseif ($code == 201) {
				headerHTTPStatus($code);

				$result = json_decode($result, TRUE);

				if (array_key_exists("errorMessage", $result)) {
					$_SESSION[$result["errorMessage"]] = $result["data"]["metadata"];
				}
			} elseif ($code == 304) {
				headerHTTPStatus($code);

				if ($cacheStore && $CURLOPT_HEADER)
					$result = $cache->get($httpHeaders["Etag"]);
			} else {
				headerHTTPStatus($code);
				throw new Exception(HTTPStatusCode($code), $code);
			}

			if ($CURLOPT_HEADER) {
				if (isset($httpHeaders["Access-Control-Allow-Origin"]))
					header("Access-Control-Allow-Origin: " . $httpHeaders["Access-Control-Allow-Origin"]);

				if (isset($httpHeaders["Cache-Control"]))
					header("Cache-Control: " . $httpHeaders["Cache-Control"]);

				if (isset($httpHeaders["Expires"]))
					header("Expires: " . $httpHeaders["Expires"]);
	
				if (isset($httpHeaders["Etag"]))
					header("Etag: " . $httpHeaders["Etag"]);

				if (isset($httpHeaders["Last-Modified"]))
					header("Last-Modified: " . $httpHeaders["Last-Modified"]);
			}
		} catch (Exception $exception) {
//			$result = array("errorMessage" => $exception->getMessage());

			$result = json_decode($result, TRUE);

			$result["errorMessage"] = $exception->getMessage();
		}

		ob_end_flush();

		return $result;
	}

	/**
	 * The following method prepares an API call using a passed method name and parameters.
	 */
	public function prepareAPICall($method, $parameters = NULL, $request = "GET") {
		if (is_null($parameters))
			$parameters["noise"] = md5(rand());

		$URI = "https://" . $this->apiServer . "/" . $method . "/";

		if (isset($parameters["value"]))
			$URI .= "value/" . $parameters["value"] . "/";

		if (isset($parameters["mode"]))
			$URI .= "mode/" . $parameters["mode"] . "/";

		if (isset($parameters["node"]))
			$URI .= "node/" . $parameters["node"] . "/";

		if (isset($parameters["oid"]))
			$URI .= "oid/" . $parameters["oid"] . "/";

		unset($this->parameters["controller"]);
		unset($this->parameters["action"]);

		$result = $this->sendRequest($URI, $parameters, $request);

		return $result;
	}
}