<?php

/**
 * test.php
 *
 * In this file there are all the usage examples useful to learn and test all the
 * class methods for Zm_Account, Zm_Server, Zm_Domain and Zm_Auth
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
 * test.php examples
 */

/////////////
// Require //
/////////////

require_once("config.php");

require_once("Zm/Auth.php");
require_once("Zm/Account.php");
require_once("Zm/Domain.php");
require_once("Zm/Server.php");

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
	$account_name = $args["str"]. "@" . $domain;
	$domain_name = "domainsoap" . $args["str"] . ".com";
	$server_name = "serversoap." . $domain_name;
}
else
{
	$rand = rand(111111, 999999999);
	$account_name = "acct_" . $rand . "@" . $domain;
	$domain_name = "dom" . $rand . ".com";
	$server_name = "srv" . $rand . "." . $domain_name;
}

if(isset($args["onam"]))
	$nam_opt = $args["onam"];
if(isset($args["oval"]))
	$val_opt = $args["oval"];


///////////
// Login //
///////////

$auth = new Zm_Auth($zimbraserver, $zimbraadminemail, $zimbraadminpassword);
$l = $auth->login();
if(is_a($l, "Exception")) {
	echo "Error : cannot login to $zimbraserver :-(\n";
	print_exception($l);
	exit();
}


/////////////
// Account //
/////////////

$accountManager = new Zm_Account($auth);

// Get All Accounts
if($action == "gaa")
{
	// warning: this may take a long time to complete and fail
	// if you have a lot of accounts on your server
	$r = $accountManager->getAllAccounts($domain);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the list of accounts for domain $domain :-(\n";
		print_exception($r);
	} else {
		//print_var($r, "Get All Accounts");
		echo "OK : got a list of ".count($r)." accounts for domain $domain :-)\n";
	}
}

// Fetch Accounts via LDAP
if($action == "gafl")
{
	// you may fetch any attribute, just specify them in this array
	$accountAttrs = array(
		'zimbraId',
		'zimbraMailDeliveryAddress',
		'zimbraAccountStatus',
		'sn',
		'givenName',
		'zimbraHideInGal',
		'mail',
		'zimbraCalResType',
		'zimbraMailQuota',
		'zimbraCreateTimestamp',
		'zimbraIsAdminAccount',
		'zimbraIsSystemResource',
	);
	// you may fetch one or more accounts using the appropriate LDAP filters
	// for more info check SearchDirectoryRequest in
	// https://people.mozilla.org/~justdave/zmsoapdocs/soap-admin.txt
	// try a search by e-mail or other attributes
	//    using "wilcard expressions" like '*', 'za*@my-domain' etc...
	$searchValue = $args["str"]. "*@" . $domain;
	$ldapFilter = sprintf("|(zimbraMailDeliveryAddress=%s)(zimbraMailAlias=%s)", $searchValue, $searchValue);
	$r = $accountManager->fetchAccounts($ldapFilter, $accountAttrs);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the list of accounts via ldap for domain $domain :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get All Accounts");
		echo "OK : got a list of ".count($r)." accounts via ldap for domain $domain :-)\n";
	}
}

// Create Account
if($action == "ca")
{
	$attrs = array("sn"=>"John", "gn"=>"Doe", "l"=>"Metropolis");
	$r = $accountManager->createAccount($account_name, "aPassword42", $attrs);

	if(is_a($r, "Exception")) {
		echo "Error : account $account_name not created :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Create Account : id");
		echo "OK : account $account_name created :-)\n";
	}
}

// Account Exists
if($action == "gax")
{
	print_var($account_name, "Check Account Existence");
	$r = $accountManager->accountExists($account_name);

	if(!$r) {
		echo "NO: account $account_name doesn't exist :-(\n";
		exit();
	} else {
		echo "YES : account $account_name exists :-)\n";
	}
}

// Get Account Informations
if($action == "gai")
{
	$r = $accountManager->getAccountInfo($account_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the infos for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account Informations");
		echo "OK : got the infos for account $account_name :-)\n";
	}
}

// Get Account Options
if($action == "gaao")
{
	$r = $accountManager->getAccountOptions($account_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the options for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account Options");
		echo "OK : got the options for account $account_name :-)\n";
	}
}

// Get Account Option
if($action == "gao")
{
	if (!$nam_opt)
		$nam_opt = "zimbraMailHost";
	$r = $accountManager->getAccountOption($account_name, $nam_opt);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the option for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account Option");
		echo "OK : got the option for account $account_name :-)\n";
	}
}

// Set Account password
if($action == "sap")
{
	$r = $accountManager->setAccountPassword($account_name, "anewPassword42");

	if(is_a($r, "Exception")) {
		echo "Error : cannot change password for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Set Account Password");
		echo "OK : password changed for account $account_name :-)\n";
	}
}

// Modify Account
if($action == "ma")
{
	if (!$nam_opt)
		$nam_opt = "streetAddress";
	if (!$val_opt)
		$val_opt = "234, 5th Avenue";
	$new_attrs = array($nam_opt=>$val_opt);
	print_var($new_attrs, "Modify Account");
	$r = $accountManager->modifyAccount($account_name, $new_attrs);

	if(is_a($r, "Exception")) {
		echo "Error : cannot modify account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Modify Account : Response");
		echo "OK : modify account $account_name :-)\n";
	}
}

// Rename Account
if($action == "ra")
{
	$new_account_name = "newname_".$account_name;
	$r = $accountManager->renameAccount($account_name, $new_account_name);

	if(is_a($r, "Exception")) {
		echo "Error : account $account_name not renamed to $new_account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Rename Account");
		echo "OK : account $account_name renamed to $new_account_name :-)\n";
	}
}

// Delete Account
if($action == "da")
{
	$r = $accountManager->deleteAccount($account_name);

	if(is_a($r, "Exception")) {
		echo "Error : account $account_name not deleted :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Delete Account : Response");
		echo "OK : account $account_name deleted :-)\n";
	}
}

// Get Account Status
if($action == "gat")
{
	$r = $accountManager->getAccountStatus($account_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the status for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account Status");
		echo "OK : got the status for account $account_name :-)\n";
	}
}

// Set Account Status
if($action == "sat")
{
	if (!$val_opt)
		$val_opt = ($accountManager->getAccountStatus($account_name) == "active") ? "locked" : "active";
	print_var($val_opt, "Set Account Status");
	$r = $accountManager->setAccountStatus($account_name, $val_opt);

	if(is_a($r, "Exception")) {
		echo "Error : cannot modify status for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Set Account Status : Response");
		echo "OK : modified status for account $account_name :-)\n";
	}
}

// Get Account COS name
if($action == "gacn")
{
	$r = $accountManager->getAccountCos($account_name, "NAME");

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the COS name for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account COS Name");
		echo "OK : got the COS name for account $account_name :-)\n";
	}
}

// Get Account COS id
if($action == "gaci")
{
	$r = $accountManager->getAccountCos($account_name, "ID");

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the COS id for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account COS Id");
		echo "OK : got the COS id for account $account_name :-)\n";
	}
}

// Set Account COS
if($action == "sac")
{
	if (!$val_opt)
		$val_opt = "testcos";
	print_var($val_opt, "Set Account COS");
	$r = $accountManager->setAccountCOS($account_name, $val_opt);

	if(is_a($r, "Exception")) {
		echo "Error : cannot modify COS for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Set Account COS : Response");
		echo "OK : modified COS for account $account_name :-)\n";
	}
}

// Add account alias
if($action == "aaa")
{
	$alias = "alias_" . $account_name;
	$r = $accountManager->addAccountAlias($account_name, $alias);

	if(is_a($r, "Exception")) {
		echo "Error : cannot add alias for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Add account alias");
		echo "OK : added alias to account $account_name :-)\n";
	}
}

// Get account aliases
if($action == "gal")
{
	$r = $accountManager->getAccountAliases($account_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the  alias for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Account Aliases");
		echo "OK : got the alias for account $account_name :-)\n";
	}
}

// Remove account alias
if($action == "raa")
{
	$alias = "alias_" . $account_name;
	$r = $accountManager->removeAccountAlias($account_name, $alias);

	if(is_a($r, "Exception")) {
		echo "Error : cannot remove alias for account $account_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Remove account alias");
		echo "OK : removed alias to account $account_name :-)\n";
	}
}


////////////
// Domain //
////////////

$domainManager = new Zm_Domain($auth);

// Get All Domains
if($action == "gad")
{
	$r = $domainManager->getAllDomains();

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the list of domains :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get All Domains");
		echo "OK : got the the list of domains :-)\n";
	}
}

// Create Domain
if($action == "cd")
{
	$r = $domainManager->createDomain($domain_name);

	if(is_a($r, "Exception")) {
		echo "Error : creating domain $domain_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Create Domain : id");
		echo "OK : domain $domain_name created :-)\n";
	}
}

// Domain Exists
if($action == "gdx")
{
	print_var($domain_name, "Check Domain Existence");
	$r = $domainManager->domainExists($domain_name);

	if(!$r) {
		echo "NO: domain $domain_name doesn't exist :-(\n";
		exit();
	} else {
		echo "YES : domain $domain_name exists :-)\n";
	}
}

// Get Domain Options
if($action == "gdao")
{
	$r = $domainManager->getDomainOptions($domain_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the options for domain $domain_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Domain Options");
		echo "OK : got the options for domain $domain_name :-)\n";
	}
}

// Modify Domain
if($action == "md")
{
	if (!$nam_opt)
		$nam_opt = "zimbraGalLdapPageSize";
	if (!$val_opt)
		$val_opt = 222;
	$new_attrs = array($nam_opt=>$val_opt);
	print_var($new_attrs, "Modify Domain : setting");
	$r = $domainManager->modifyDomain($domain_name, $new_attrs);

	if(is_a($r, "Exception")) {
		echo "Error : modifying domain $domain_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Modify Domain : Response");
		echo "OK : domain $domain_name modified :-)\n";
	}
}

// Delete Domain
if($action == "dd")
{
	$r = $domainManager->deleteDomain($domain_name);

	if(is_a($r, "Exception")) {
		echo "Error : deleting domain $domain_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Delete Domain : Response");
		echo "OK : domain $domain_name deleted :-)\n";
	}
}


////////////
// Server //
////////////

$serverManager = new Zm_Server($auth);

// Get All Servers
if($action == "gas")
{
	$r = $serverManager->getAllServers();

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the list of servers for domain $domain_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get All Servers");
		echo "OK : got the list of servers for domain $domain_name :-)\n";
	}
}

// Create Server
if($action == "cs")
{
	$r = $serverManager->createServer($server_name);

	if(is_a($r, "Exception")) {
		echo "Error : creating server $server_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Create Server : id");
		echo "OK : server $server_name created :-)\n";
	}
}

// Server Exists
if($action == "gsx")
{
	print_var($server_name, "Check Server Existence");
	$r = $serverManager->serverExists($server_name);

	if(!$r) {
		echo "NO: server $server_name doesn't exist :-(\n";
		exit();
	} else {
		echo "YES : server $server_name exists :-)\n";
	}
}

// Get Server Options
if($action == "gsao")
{
	$r = $serverManager->getServerOptions($server_name);

	if(is_a($r, "Exception")) {
		echo "Error : cannot fetch the options for server $server_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Get Server Options");
		echo "OK : got the options for server $server_name :-)\n";
	}
}

// Modify Server
if($action == "ms")
{
	if (!$nam_opt)
		$nam_opt = "zimbraHttpSSLNumThreads";
	if (!$val_opt)
		$val_opt = 333;
	$new_attrs = array($nam_opt=>$val_opt);
	print_var($new_attrs, "Modify Server : setting");
	$r = $serverManager->modifyServer($server_name, $new_attrs);

	if(is_a($r, "Exception")) {
		echo "Error : modifying server $server_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Modify Server : Response");
		echo "OK : server $server_name modified :-)\n";
	}
}

// Delete Server
if($action == "ds")
{
	$r = $serverManager->deleteServer($server_name);

	if(is_a($r, "Exception")) {
		echo "Error : deleting server $server_name :-(\n";
		print_exception($r);
	} else {
		print_var($r, "Delete Server : Response");
		echo "OK : server $server_name deleted :-)\n";
	}
}

if(!$r) echo "Invalid action!\n";

?>
