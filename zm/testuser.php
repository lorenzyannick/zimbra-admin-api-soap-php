<?php

/**
 * testuser.php
 *
 * In this file there are all the usage examples useful to learn and test all the
 * class methods for Zm_User
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @author Fabrizio La Rosa <fabrizio.larosa@unime.it>
 * @version 2.1
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @copyright Copyright (c) 2012, Fabrizio La Rosa
 * @name test.php
 * @filesource
 */
/**
 * testuser.php examples
 */

/////////////
// Require //
/////////////

require_once("config.php");

require_once("Zm/Auth.php");
require_once("Zm/User.php");

//////////
// Args //
//////////

if(PHP_SAPI != "cli")
	$args = $_GET;
else
	$args = parse_args($argv);

if(isset($args["action"]))
{
	$action = $args["action"];
}
else
{
	echo "No action, exiting\n";
	exit (-1);
}

if(isset($args["str"]))
{
	$user_name = $args["str"]. "@" . $domain;
}

if(isset($args["onam"]))
	$nam_opt = $args["onam"];
if(isset($args["oval"]))
	$val_opt = $args["oval"];


///////////
// Login //
///////////

$userpassword = "Password42";
$auth = new Zm_Auth($zimbraserver, $user_name, $userpassword, "user");
$l = $auth->login();
if(is_a($l, "Exception")) {
	echo "Error : cannot login to $zimbraserver :-(\n";
	print_exception($l);
	exit();
}


//////////
// User //
//////////

$userManager = new Zm_User($auth);

// User Exists
if($action == "gux")
{
	print_var($user_name, "Check User Existence");
	$r = $userManager->userExists($user_name);

	if(!$r) {
		echo "NO: user $user_name doesn't exist :-(\n";
		exit();
	} else {
		echo "YES : user $user_name exists :-)\n";
	}
}

// Get User Informations
if($action == "gui")
{
	$r = $userManager->getUserInfo($user_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the infos for user $user_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get User Informations");
		echo "OK : got the infos for user $user_name :-)\n";
	}
}

// Get User Prefs
if($action == "gup")
{
	$r = $userManager->getUserPrefs($user_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch prefs for user $user_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get User Prefs");
		echo "OK : got prefs for user $user_name :-)\n";
	}
}

// Get User Attrs
if($action == "gua")
{
	if (!$nam_opt)
		$nam_opt = "zimbraMailHost";
	$r = $userManager->getUserAttrs($user_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch attrs for user $user_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get User Attrs");
		echo "OK : got attrs for user $user_name :-)\n";
	}
}

// Change User password
if($action == "cup")
{
  	$r = $userManager->changeUserPassword($user_name, $userpassword, "newPassword42");

	if(is_a($r, "Exception")) {
		echo "Error : cannot change password for user $user_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Change User Password");
		echo "OK : password changed for user $user_name :-)\n";
	}
}

// Modify Prefs
if($action == "mup")
{
	if (!$nam_opt)
		$nam_opt = "zimbraPrefMailItemsPerPage";
	if (!$val_opt)
		$val_opt = "42";
	$new_prefs = array($nam_opt=>$val_opt);
	print_var($new_prefs, "Modify Prefs");
	$r = $userManager->modifyUserPrefs($user_name, $new_prefs);

	if(is_a($r, "Exception")) {
		echo "Error : cannot modify prefs for $user_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Modify Prefs : Response");
		echo "OK : modify user prefs for $user_name :-)\n";
	}
}

if(!$r) echo "Invalid action!\n";

?>
