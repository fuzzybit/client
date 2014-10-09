<?php

	/**
	 * This file introduces a Configuration class.
	 *
	 * @package	FuzzyBit XOO
	 */

	/**
	 * Class which opens/saves a simple set of configurations
	 *
	 * <p>Upon declaring a new GlobalConfiguration object, the configurations are immediately loaded into an array for later reference.</p>
	 * <p>The object creates a copy of the configuration file before the array is saved back to the global.xml configuration file.</p>
	 *
	 * @package	FuzzyBit XOO
	 */
	class Configuration
	{
		/**
		 *
		 */
		const file = "global.xml";
		/**
		 * This variable stores a handle to a file.
		 *
		 * @access	private
		 * @var		object
		 */
		private $handle = null;
		/**
		 * This variable stores name/values of configuration settings.
		 *
		 * @access	private
		 * @var		mixed
		 */
		private $configurations = array();
		/**
		 *
		 */
		private $dom;

		/**
		 * This constructor accepts a filename, loads the XML configuration file and stores the configuration settings.
		 *
		 * @param	string $file
		 */
		function __construct($file = self::file)
		{
			$this->dom = new DOMDocument();
			$this->dom->load(dirname(__FILE__) . '/' . $file);

			$this->loadConfigurations("maintenance");
			$this->loadConfigurations("application");
			$this->loadConfigurations("hosts");
			$this->loadConfigurations("database");
			$this->loadConfigurations("frontController");
			$this->loadConfigurations("credentials");

			unset($this->dom);
		}

		/**
		 * FOR FUTURE IMPLEMENTATION
		 */
		function __destruct()
		{
		}

		/**
		 * This magic getter retrieves a configuration setting.
		 *
		 * @param	string $id
		 * @return	mixed
		 */
		function __get($id)
		{
			return $this->configurations[$id];
		}

		/**
		 * This magic setter sets configuration parameters.
		 *
		 * @param	string $id
		 * @param	string $value
		 */
		function __set($id, $value)
		{
			$this->configurations[$id] = $value;
		}

		/**
		 * This method returns the number of configuration settings.
		 *
		 * @return	integer
		 */
		function count()
		{
			return count($this->configurations);
		}

		/**
		 *
		 */
		function loadConfigurations($tagName) {
			$nodes = $this->dom->getElementsByTagName($tagName)->item(0)->getElementsByTagName("*");

			try {
				foreach ($nodes as $node) {
					if (isset($this->configurations[$node->nodeName]))
						throw new Exception("Configuration setting has previously been set - duplicate setting not allowed.");

					$this->configurations[$node->nodeName] = $node->nodeValue;
				}
			} catch (Exception $error) {

			}
		}

		/**
		 * This method saves the configuration settings in an array to a file.
		 */
		function save()
		{
			copy(self::file, self::file . '.bak');

			$dom = new DOMDocument();
			$dom->formatOutput = true;

			$root = $dom->createElement("configuration");
			$dom->appendChild($root);

			$node = $dom->createElement("database");
			$root->appendChild($node);

			foreach ($this->configurations as $key => $value)
			{
				$child = $dom->createElement($key);
				$child->appendChild($dom->createTextNode($value));
				$node->appendChild($child);
			}

			$dom->save(self::file);

			unset($dom);
		}
	}