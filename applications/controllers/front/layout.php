<?php

	/**
	 * This file introduces MVC controller classes
	 *
	 * @package	FuzzyBit XOO
	 */

	/**
	 * This class specifies MVC action methods.
	 *
	 * @package	FuzzyBit XOO
	 */
	class mode implements IController
	{
		/**
		 *
		 */
		private $protocol;
		/**
		 *
		 */
		private $domain;
		/**
		 *
		 */
		private $defaultRequest;
		/**
		 *
		 */
		private $defaultValue;

		/**
		 *
		 */
		public function __construct() {
			$configuration = Container::newConfiguration();

			$this->protocol = $configuration->protocol;
			$this->domain = $configuration->domainName;
			$this->defaultRequest = $configuration->defaultRequest;
			$this->defaultValue = $configuration->defaultValue;
		}

		/**
		 * This method instantiates the MVC front-controller class and action-view to render a page.
		 */
		public function edit()
		{
			$frontController = FrontController::getInstance($this->defaultRequest);
			$URI = $this->protocol . "://" . $this->domain . "/" . $frontController::$request;

			$frontController->mode = "edit";
			$frontController->value = $this->defaultValue;

			$view = new View($URI, $frontController->params);

			$frontController->body = $view->render("../views/index.php");
		}
	}

	/**
	 * This class specifies MVC action methods
	 *
	 * @package	FuzzyBit XOO
	 */
	class XOO implements IController
	{
		/**
		 *
		 */
		private $protocol;
		/**
		 *
		 */
		private $domain;
		/**
		 *
		 */
		private $defaultRequest;
		/**
		 *
		 */
		private $defaultValue;
		/**
		 * This variable is a private parameter storing the URI of a page.
		 *
		 * @access	private
		 * @var		string
		 */
		private $URI;
		/**
		 * This private variable stores the application ID;
		 */
		private $applicationID;
		/**
		 * This private variable stores the application key;
		 */
		private $applicationKey;
		/**
		 *
		 */
		private $apiMethod;
		/**
		 *
		 */
		private $httpMethod;

		/**
		 * This utility function returns a boolean after it checks whether the token is valid.
		 */
		private function checkToken($request) {
			$result = "";

			$_blnValid = isset($request["token"]);

			if ($_blnValid) {
				$explode = explode(".", $request["token"]);

				$_blnValid = (isset($_SESSION["tokens"]) && isset($_SESSION["tokens"][$explode[0]]));

				if ($_blnValid) {
					$_blnValid = ($_SESSION["tokens"][$explode[0]] == urldecode($explode[1]));

					if (!$_blnValid)
						$result = "Token mismatch.";

					unset($_SESSION["tokens"][$explode[0]]);
				} else {
					$result = "Session tokens are not set.";
				}
			} else {
				$result = "Post token is not set.";
			}

			return $result;
		}

		/**
		 *
		 */
		public function __construct() {
			$configuration = Container::newConfiguration();

			$this->protocol = $configuration->protocol;
			$this->domain = $configuration->domainName;
			$this->defaultRequest = $configuration->defaultRequest;

			$this->applicationID = $configuration->id;
			$this->applicationKey = $configuration->secretKey;
		}

		/**
		 *
		 */
		public function getAPIMethod() {
			return $this->apiMethod;
		}

		/**
		 *
		 */
		public function getHTTPMethod() {
			return $this->httpMethod;
		}

		/**
		 *
		 */
		public function prepareAPICall($apiMethod, $parameters = NULL, $httpMethod = NULL) {
			$api = new APICaller();

			$result = $api->prepareAPICall($apiMethod, $parameters, $httpMethod);

			return $result;
		}

		/**
		 *
		 */
		public function view($URI, $data) {
			$view = new View($URI, $data);

			$result = $view->render("../views/index.php");

			return $result;
		}

		/**
		 * This method renders an `index` action.
		 */
		public function index()
		{
			$frontController = FrontController::getInstance($this->defaultRequest);
			$this->URI = $this->protocol . "://" . $this->domain . "/" . $frontController::$request;

			$view = new View($this->URI);

			$frontController->body = $view->render("../../php5/files/index.html");
		}

		/**
		 * This method renders a `sprite` action method and publishes files to a local directory.
		 */
		public function sprite($frontController, $isXHR)
		{
			$this->URI = $this->protocol . "://" . $this->domain . "/" . $frontController::$request;

			if (isset($frontController->params["error"]) && isset($_SESSION[$frontController->params["error"]])) {
				$params = $_SESSION[$frontController->params["error"]];

				unset($_SESSION[$frontController->params["error"]]);
			} else {
				$params = $frontController->params;
			}

			$view = new View($this->URI, $params);

			$frontController->body = $view->render("../views/index.php");
		}

		/**
		 * The method prepares a call to an API URI with front controller parameters.
		 */
		public function api($frontController)
		{
			$method = "index";
			if (isset($frontController->params["method"]))
				$method = $frontController->params["method"];
			$parameters = $frontController->params + $_POST;

			$result = $this->prepareAPICall($method, $parameters);

			$frontController->body = json_encode($result);
		}

		/**
		 * This method renders a `mode` action method that saves the parameters of submitted forms.
		 *
		 * @param	array $parameters
		 */
		public function mode($frontController, $isXHR)
		{
			$result = array();
			$result["data"] = NULL;
			$result["errorMessage"] = $this->checkToken($_POST);

			$_blnValid = empty($result["errorMessage"]);

			if ($_blnValid) {
				$parameters = $frontController->params + $_POST;

				$method = "";
				$httpMethod = "";
				if (isset($parameters["mode"]) && isset($parameters["node"]) && !isset($parameters["oid"])) {
					$method = "contentStyleScript";
					$httpMethod = "PUT";
				} elseif (isset($parameters["node"]) && isset($parameters["oid"]) && isset($parameters["delete"]) && ($parameters["delete"] == "on")) {
					$method = "formData";
					$httpMethod = "DELETE";
				} elseif (isset($parameters["node"]) && isset($parameters["oid"])) {
					$method = "formData";
					$httpMethod = "PUT";
				} elseif (isset($parameters["node"])) {
					$method = "formData";
					$httpMethod = "POST";
				}

				$this->apiMethod = $method;
				$this->httpMethod = $httpMethod;

				if (!empty($httpMethod))
					$result = $this->prepareAPICall($method, $parameters, $httpMethod);

				if (array_key_exists("errorMessage", $result)) {
					if ($isXHR)
						$result = json_encode($result);
					else
						$result = $result["errorMessage"];
				} else {
					if ($isXHR) {
						$result = array("result" => $_POST["token"],
								"data" => $result["data"],
								"errorMessage" => isset($result["errorMessage"]) ? $result["errorMessage"] : "",
								"dateTime" => $_POST["dateTime"]);

						$result = json_encode($result);
					} else {
						$this->URI = $this->protocol . "://" . $this->domain . "/" . $frontController::$request;

						// TO DO:	Implement error parameters
						// 		Check for $result["data"]["errorMessage"]
						// if (array_key_exists("errorMessage", $result) && (preg_match('/^0x[0-9a-f]{8}$/i', $result["errorMessage"]) == 1))
						// 	$this->URI .= "/error/" . $result["errorMessage"];

						$result = $this->view($this->URI, $frontController->params);
					}
				}
			} else {
				$result = json_encode($result);
			}

//			$frontController->body = $result;

			return $result;
		}

		/**
		 * This method renders a `mode` action method that saves the parameters of submitted forms.
		 *
		 * @param	array $parameters
		 */
		public function patch($frontController, $isXHR)
		{
			$result = array();
			$result["errorMessage"] = $this->checkToken($frontController->params);

			$_blnValid = empty($result["errorMessage"]);

			if ($_blnValid) {
				$parameters = $frontController->params;

				$result = $this->prepareAPICall("formData", $parameters, "PATCH");

				if (array_key_exists("errorMessage", $result)) {
					if ($isXHR)
						$result = json_encode($result);
					else
						$result = $result["errorMessage"];
				} else {
					if ($isXHR) {
						$result = array("result" => $parameters["token"],
								"data" => $result["data"],
								"errorMessage" => isset($result["errorMessage"]) ? $result["errorMessage"] : "",
								"dateTime" => $parameters["dateTime"]);

						$result = json_encode($result);
					} else {
						$this->URI = $this->protocol . "://" . $this->domain . "/" . $frontController::$request;

						$result = $this->view($this->URI, $frontController->params);
					}
				}
			}

			$frontController->body = $result;

			return $result;
		}
	}
