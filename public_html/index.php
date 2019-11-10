<?php

	/**
	 * This is a bootstrap file for a MVC design pattern.
	 *
	 * @package	FuzzyBit XOO
	 */

	require_once("../php5/Container.php");
	require_once("../php5/APICaller.php");
	require_once("../php5/logic/FormSignature.php");

	require_once("../applications/models/front.php");
	require_once("../applications/models/icontroller.php");
	require_once("../applications/models/view.php");
	require_once("../applications/controllers/index.php");
	require_once("../applications/controllers/front/layout.php");
	require_once("../applications/controllers/action/ILayoutNode.php");
	require_once("../applications/controllers/action/ILayoutView.php");
	require_once("../applications/controllers/action/Layout.php");
	require_once("../applications/controllers/action/LayoutNode.php");
	require_once("../applications/controllers/action/LayoutEditView.php");
	require_once("../applications/controllers/action/LayoutView.php");
	require_once("../applications/controllers/action/Form.php");

	require_once("../applications/controllers/front/api.php");

	$front = FrontController::getInstance();
	$front->route();
	echo $front->body;
@	$layout = $front->layout;
	FrontController::destroy();

	require_once("../applications/controllers/front/MyXOOLayout.php");
	$frontController = FrontController::getInstance("MyXOOLayout/index");
@	$frontController->route();