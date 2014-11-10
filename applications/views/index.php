<?php

	$configuration = Container::newConfiguration();

	$protocol = $configuration->protocol;
	$domain = $configuration->localhost . $configuration->client;

	$administratorIP = $configuration->administratorIP;
	$isAdministrator = ($_SERVER['REMOTE_ADDR'] == $administratorIP);
	$isMaintenance = ($configuration->maintenanceOn == 1);

	if ($isMaintenance && !isset($isMaintenancePage) && !$isAdministrator)
		header("Location: $protocol://$domain/oops/maintenance.php");

	$_txtScript = $this->script();

	$_txtStyle = $this->styles();

	$_txtContent = $this->layout();

	$title = "";

	$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="description" content="FuzzyBit web development is art on the page and science in the source." />
<meta name="keywords" content="web, web development, web design, web programming, HTML5, CSS3, Javascript, PHP, Ajax, MySQL" />
<meta name="author" content="FuzzyBit" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Merienda+One" />
<link rel="stylesheet" type="text/css" href="$protocol://$domain/styles/css.css" />
<link rel="stylesheet" type="text/css" href="$protocol://$domain/styles/profiles.css" />
<link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="$protocol://$domain/styles/iPhone.css" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px)" href="$protocol://$domain/styles/iPad.css" />
<link rel="stylesheet" type="text/css" media="print" href="$protocol://$domain/styles/print.css" />
<link rel="stylesheet" type="text/css" media="print" href="$protocol://$domain/styles/weloveiconfonts.css" />
<style>
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
<title>FuzzyBit &#8226; $title</title>
</head>
<body>
<div class="edgeLogo" style="left: -10px; width: 60px;"><img src="$protocol://$domain/images/logo.jpg" alt="logo" class="logoImage" style="left: -280px;" /></div>
<div class="edgeLogo" style="left: auto; right: -10px; width: 60px;"><img src="$protocol://$domain/images/logo.jpg" alt="logo" class="logoImage" style="left: 0px;" /></div>

<a href="$protocol://$domain/"><div id="fuzzybit"><span id="fuzzy">fuzzy</span><span id="bit">BIT</span>&nbsp;<span id="motto">blurring the bounds of the web</span> BETA</div></a>

<div id="topLine">$title&nbsp;<img src="$protocol://$domain/images/01.png" alt="small fuzzy dot" /><img src="$protocol://$domain/images/10.png" alt="fuzzy dot" />&nbsp;$title&nbsp;<img src="$protocol://$domain/images/10.png" alt="fuzzy dot" /><img src="$protocol://$domain/images/01.png" alt="small fuzzy dot" />&nbsp;$title</div>

$_txtContent

<div class="footerLine print" id="upperFooter">
<a href="$protocol://$domain/">Home</a>&nbsp;&#8226;&nbsp;<a href="$protocol://$domain/contact.php">Contact</a>
</div>
<div class="footerLine" id="lowerFooter">FuzzyBit Software Inc.</div>
<script src="$protocol://$domain/javascript/mind-map.js" onload="init();"></script>
</body>
</html>
HTML;

	echo $html;

?>
