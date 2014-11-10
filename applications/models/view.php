<?php

	/**
	 * This files introduces the View class in the MVC design pattern.
	 *
	 * @package	FuzzyBit XOO
	 */

	/**
	 * This class receives an URI and parameters and instantiates a LayoutView.
	 *
	 * @package	FuzzyBit XOO
	 */
	class View extends ArrayObject
	{
		/**
		 * Default token name of editor form class.
		 */
		const editorToken = "PageURI";
		/**
		 * This variable stores an URI.
		 *
		 * @access	private
		 * @var		string
		 */
		private $URI;
		/**
		 * This variable stores URI parameters.
		 *
		 * @access	private
		 * @var		array
		 */
		private $parameters;
		/**
		 *
		 */
		private $localDirectory;
		/**
		 * This variable holds the results of an API call.
		 */
		private $result;

		/**
		 * This constructor accepts an URI and parameters and instantiates a LayoutView.
		 *
		 * @param	string $URI
		 * @param	array $parameters
		 */
		public function __construct($URI, $parameters = NULL)
		{
			$configuration = Container::newConfiguration();

			$this->localDirectory = $configuration->localDirectory;

			parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);

			$this->URI = $URI;
			$this->parameters = $parameters;

			$api = new APICaller();
			$this->result = $api->prepareAPICall("contentStyleScript", $this->parameters, "GET");

			if (isset($this->result["tokens"])) {
				foreach ($this->result["tokens"] as $key => $value)
					$_SESSION["tokens"][$key] = $value;
			}

			$mode = isset($this->parameters["mode"]) && $this->parameters["mode"] == "edit";
			$mode = $mode && !isset($this->parameters["node"]);
			$mode = $mode && !isset($this->parameters["oid"]);
			if ($mode) {
				$this->parameters["value"] = 17;

				$result = $api->prepareAPICall("contentStyleScript", $this->parameters, "GET");

				if (isset($result["tokens"])) {
					foreach ($result["tokens"] as $key => $value)
						$_SESSION["tokens"][$key] = $value;

					setcookie("tokenID", $key . "." . $value, time() + 24 * 60 * 60, "/", "", TRUE);
				}
			}
		}

		/**
		 * This function retrieves a file used for rendering a page.
		 *
		 * @param	string $file
		 * @return	string
		 */
		public function render($file)
		{
			ob_start();
			include(dirname(__FILE__) . '/' . $file);
			return ob_get_clean();
		}

		/**
		 * This method is a wrapper calling the LayoutView's layout parameter. The commented code, ie. session_status() and PHP_SESSION_ACTIVE, is available in PHP 5.4.
		 *
		 * @return	string
		 */
		public function layout()
		{
			return $this->result["data"]["content"];
		}

		/**
		 * This method is a wrapper calling the LayoutView's styles parameter.
		 *
		 * @return	string
		 */
		public function styles()
		{
			return $this->result["data"]["style"];
		}

		/**
		 * This method is a wrapper calling the LayoutView's script parameter.
		 *
		 * @return	string
		 */
		public function script()
		{
			return $this->result["data"]["script"];
		}

		/**
		 * This method saves calls LayoutView's object method to save files, and subsequently save the full page.
		 */
		public function publish()
		{
			if (isset($this->parameters["node"]))
			{
				$path = $this->localDirectory . "/php5/files/";
				$filename = str_replace(".", "_", $this->parameters["node"]);

				$handle = fopen($path . $filename . ".html", "w");
				fwrite($handle, $this->layout());
				fclose($handle);

				$handle = fopen($path . $filename . ".css", "w");
				fwrite($handle, $this->styles());
				fclose($handle);

				$handle = fopen($path . $filename . ".js", "w");
				fwrite($handle, $this->script());
				fclose($handle);

				/** FOR FUTURE IMPLEMENTATION
				$handle = fopen($path . $filename . ".xml", "w");
				fwrite($handle, $this->XML);
				fclose($handle);
				FOR FUTURE IMPLEMENTATION **/

				$filename = explode(".", $this->parameters["node"]);
				$handle = fopen($path . $filename[0] . ".html", "w");
				$content = $this->render("../views/index.php");
				fwrite($handle, $content);
				fclose($handle);
			}
		}
	}
