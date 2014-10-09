<?php

	/**
	 * This file declares the Container class.
	 *
	 * @package	FuzzyBit XOO
	 */

	include_once('Configuration.php');
	/**
	 * This is a 'container' class modelled on 'dependency injection'.
	 *
	 * @package	FuzzyBit XOO
	 */
	class Container
	{
		/**
		 * This constant is the configuration filename.
		 */
		const file = 'global.xml';

		/**
		 * This variable is Container object.
		 *
		 * @access	public
		 * @var		object
		 */
		public static $configuration;
		/**
		 * This variable is the data source connection.
		 *
		 * @access	public
		 * @var		object
		 */
		public static $connection;

		/**
		 * FOR FUTURE IMPLEMENTATION
		 */
		function __construct()
		{
		}

		/**
		 * This static constructor instantiates a new Configuration object.
		 *
		 * @return	object
		 */
		public static function newConfiguration()
		{
			self::$configuration = new Configuration(self::file);

			return self::$configuration;
		}

		/**
		 * This function calls the front controller to echo the resource in the calling page.
		 *
		 * @param	string $URI
		 */
		public static function newFrontInstance($URI)
		{
			ini_set('session.cookie_secure', TRUE);	
			session_start();

			$front = FrontController::getInstance($URI);

			$front->route();

			ob_start("ob_gzhandler");

			echo $front->body;

			FrontController::destroy();

			session_write_close();
		}

		/**
		 * This static method calls LayoutView's object to save various c/s/s and HTML files.
		 *
		 * @param	string $URI
		 * @param	string $mode
		 * @param	string $node
		 */
		public static function publishLayout($URI, $mode, $node)
		{
			$connection = self::newConnection();

			$layoutView = new LayoutView($URI, $mode, $node);
			$layoutView->setConnection($connection);
			$layoutView->initialize();
			$layoutView->publish();
		}
	}