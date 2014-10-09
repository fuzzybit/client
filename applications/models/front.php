<?php

	/**
	 * This file introduces the FrontController class in an MVC design pattern.
	 *
	 * @package	FuzzyBit XOO
	 */

	/**
	 * This class retrieves an URI and parses it to determine the controller class and action method to call.
	 *
	 * @package	FuzzyBit XOO
	 */
	class FrontController
	{
		/**
		 * GET
		 */
		const GET = 1;
		/**
		 * POST
		 */
		const POST = 2;
		/**
		 * PUT
		 */
		const PUT = 4;
		/**
		 * DELETE
		 */
		const DELETE = 8;
		/**
		 * PATCH
		 */
		const PATCH = 16;
		/**
		 * OPTIONS
		 */
		const OPTIONS = 32;
		/**
		 * HEAD
		 */
		const HEAD = 64;
		/**
		 * XHR
		 */
		const XHR = 128;

		/**
		 * CONTROLLER
		 */
		const CONTROLLER = "xoo";
		/**
		 * ACTION
		 */
		const ACTION = "sprite";

		/**
		 * This variable is an array that stores the name/value pairs of an URI.
		 *
		 * @access	protected
		 * @var		array
		 */
		protected $_params;

		/**
		 * This variable is the self's object.
		 *
		 * @access	static
		 * @var		object
		 */
		static $_instance;
		/**
		 * This variable is the unparsed URI.
		 *
		 * @access	static
		 * @var		string
		 */
		public static $request;
		/**
		 *
		 */
		public static $requestType = 0;
		/**
		 * HTTP 404 value
		 */
		public static $http404;
		/**
		 * Default URI value: '/value/[VALUE]'
		 */
		public static $defaultValue;

		/**
		 * This function parses an URI to extract name/value pairs of an URI.
		 *
		 * @param	string $request
		 * @return	object
		 */
		public static function getInstance($request = NULL)
		{
			$configuration = new Configuration();

			if (!is_null($request))
			{
				self::$request = $request;
			}
			else
			{
				if (isset($_SERVER['REQUEST_URI']))
					$request = $_SERVER['REQUEST_URI'];

				if (!empty($request)) {
					self::$request = $request;
				} else {
					self::$request = $configuration->defaultRequest;
				}
			}

			self::$request = str_replace($configuration->client, "", self::$request);
			self::$request = trim(self::$request, "/");

			self::$defaultValue = $configuration->defaultValue;

			self::$http404 = $configuration->http404;

			if(!(self::$_instance instanceof self))
				self::$_instance = new self();

			return self::$_instance;
		}

		public static function destroy()
		{
			if(self::$_instance instanceof self)
				self::$_instance = NULL;
		}

		/**
		 * This constructor parses a filtered URI and stores it.
		 */
		private function __construct()
		{
			switch ($_SERVER["REQUEST_METHOD"]) {
				case "GET":
					self::$requestType = self::GET;
					break;
				case "POST":
					self::$requestType = self::POST;
					break;
				case "PUT":
					self::$requestType = self::PUT;
					break;
				case "DELETE":
					self::$requestType = self::DELETE;
					break;
				case "PATCH":
					self::$requestType = self::PATCH;
					break;
				default:
					throw new Exception("Request not understood.");
			}

			if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"))
				self::$requestType += self::XHR;

			/**
			 * INITIALIZE FILE HANDLING FOR LOGS (SplFileInfo AND SplFileObject)
			 */

			/**
			 * HERE IS REQUIRED CHARACTER FILTERING 
			 * http://php.net/manual/en/book.filter.php
			 */

			$explode = explode('/', self::$request);

			$this->_params["controller"] = empty($explode[0]) ? self::CONTROLLER : $explode[0];
			$this->_params["action"] = empty($explode[1]) ? self::ACTION : $explode[1];
			$this->_params["value"] = empty($explode[3]) ? self::$defaultValue : $explode[3];
			$this->_params["body"] = "";

			for ($index = 1, $count = count($explode); (2 * $index) < $count; $index++)
				$this->_params[$explode[2 * $index]] = isset($explode[2 * $index + 1]) ? $explode[2 * $index + 1] : NULL;
		}

		/**
		 * Simple utility function to check for strict high-bits of datum against supplied high-bits of flags.
		 *
		 * @param	integer $datum
		 * @param	integer $flags
		 */
		private function checkFlags($datum, $flags) {
			return (($datum & $flags) == $flags) || (($datum & ($flags | self::XHR)) == ($flags | self::XHR));
		}

		/**
		 * This magic getter method returns one or all parameters.
		 *
		 * @param	array $param
		 * @return	mixed
		 */
		public function __get($param)
		{
			if ($param == "params")
				return $this->_params;
			else
				return isset($this->_params[$param]) ? $this->_params[$param] : NULL;
		}

		/**
		 * This magic setter sets a parameter.
		 *
		 * @param	array $param
		 * @param	mixed $value
		 */
		public function __set($param, $value)
		{
			if ($param == "params")
				$this->_params = $value;
			else
				$this->_params[$param] = $value;
		}

		/**
		 * This method calls instantiates a class and calls a method based on URI input.
		 */
		public function route()
		{
			$oid = NULL;

			try {
				if (class_exists($this->_params["controller"]))
				{
					$reflection = new ReflectionClass($this->_params["controller"]);

					if ($reflection->implementsInterface("IController"))
					{
						$controller = $reflection->newInstance();

						$isXHR = (self::$requestType & self::XHR) == self::XHR;

						if ($this->checkFlags(self::$requestType, self::PATCH)) {
							$method = $reflection->getMethod("patch");
							$oid = $method->invoke($controller, $this, $isXHR);
						} elseif ($this->checkFlags(self::$requestType, self::POST)) {
							if (isset($this->_params["mode"]) || isset($this->_params["node"]) || isset($this->_params["oid"]))
							{
								$method = $reflection->getMethod("mode");
								$oid = $method->invoke($controller, $this, $isXHR);
							}
						} else {
							if ($reflection->hasMethod($this->_params["action"]))
							{
								$method = $reflection->getMethod($this->_params["action"]);
								$method->invoke($controller, $this, $isXHR);
							}
							else
							{
								throw new Exception("Class method does not exist.");
							}
						}
					}
					else
					{
						throw new Exception("Class does not implement iController");
					}
				}
				else
				{
					// CHECK FOR ALIAS
					throw new Exception("Controller class does not exist.");
				}
			} catch (Exception $exception) {
				$this->_params["controller"] = self::CONTROLLER;
				$this->_params["action"] = self::ACTION;
				$this->_params["value"] = self::$http404;

				$oid = $this->route();
			}

			return $oid;
		}
	}