<?php

	/**
	 * This file is soon to be DEPRECATED.
	 *
	 * @package	FuzzyBit XOO
	 */

/**
	set_include_path("/home/fuzzybit/openid/:/home/fuzzybit/php/:/usr/lib/sendmail/");

	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
		ob_start("ob_gzhandler");
	else
		ob_start();
**/

	include_once("php5/global.php");

	define("e_HOME",	"http://www.fuzzybit.com/");

	/**
	 * This token formats a hidden HTML form input tag.
	 *
	 * @param	string $command
	 * @return	string
	 */
	function getHiddenToken($command) {
		$token = md5(uniqid(rand(), TRUE));

		$_SESSION[$command] = $token;

		$hidden = <<<HIDDEN
<input type="hidden" name="$command" value="$token" id="token" />
HIDDEN;

		return $hidden;
	}

	/**
	 * This function sets a session variable and returns it.
	 *
	 * @return	string
	 */
	function getToken(){
		$_SESSION["commentator"] = md5(uniqid(rand(), TRUE));

		return $_SESSION["commentator"];
	}
