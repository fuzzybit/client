<?php

	/**
	 * This file represents a skeleton HTML file used to display c/s/s of a XOO layout.
	 *
	 * @package	FuzzyBit XOO
	 */

	$_txtScript = $this->script();

	$_txtStyle = $this->styles();

	$_txtContent = $this->layout();

	$configuration = Container::newConfiguration();

	$protocol = $configuration->protocol;
	$domain = $configuration->localhost . $configuration->client;

	$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<title></title>
<link rel="stylesheet" type="text/css" href="$protocol://$domain/styles/css.css" />
<style type="text/css">
.west { position: relative; overflow: hidden; border-width: 0px; float: left; }
.east { position: relative; overflow: hidden; border-width: 0px; float: right; }
.clear { position: relative; overflow: hidden; border-width: 0px; clear: both; }

@-ms-viewport {
	width: auto!important;
}

$_txtStyle

</style>
<script type="text/javascript" src="$protocol://$domain/javascript/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/constants.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/handles.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/layout.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/core.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/js.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/ajax.js"></script>
<script type="text/javascript" src="$protocol://$domain/javascript/jq.js"></script>
<script type="text/javascript">

$_txtScript

</script>
</head>
<body>

$_txtContent

</body>
</html>
HTML;

	echo $html;