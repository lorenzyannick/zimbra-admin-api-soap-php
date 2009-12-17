<?php
/////////////
// Require //
/////////////
require_once('ZmAuth.php');

require_once('ZmAccount.php');
require_once('ZmDomain.php');
require_once('ZmServer.php');

//require_once('ZmSoapFault.php');
require_once("utils.php");
require_once("config.php");

////////////
// Config //
////////////
//$zimbraserver = "zmd.1g6.biz";
//$zimbraadminemail = "admin@zmd.1g6.biz";
//$zimbraadminpassword = "IgbDh2GN";

/////////
// Get //
/////////
if(isset($_GET['nom']) && isset($_GET['newnom']))
{
	$nom_compte = "test_soap_" . $_GET['nom']. "@" . $zimbraserver;
	$new_nom_compte = "test_soap_" . $_GET['newnom']. "@" . $zimbraserver;

	$nom_domaine = $_GET['nom'];
	$new_nom_domaine = $_GET['newnom'];

	$nom_serveur = $_GET['nom'];
        //$new_nom_serveur = $_GET['newnom'];
}
else
{
	$rand = rand(0, 9999999999999);
	$nom_compte = "test_soap_" . $rand . "@" . $zimbraserver;
	$new_nom_compte = "new_test_soap_" . $rand . "@" . $zimbraserver;

	$nom_domaine = "d" . $rand . ".com";
	$new_nom_domaine = "newd" . $rand . ".com";

	$nom_serveur = "s" . $rand . ".com";
	//$new_nom_serveur = "news" . $rand . ".com";
}

if(isset($_GET['action']))
{
    $action = $_GET['action'];
}
else
{
    $action = null;
}

///////////
// Login //
///////////
$auth = new ZmAuth($zimbraserver, $zimbraadminemail, $zimbraadminpassword);
$auth->login();



/////////////
// Account //
/////////////
$accountManager = new ZmAccount($auth);

	// Get Account Informations
	if($action == "gai")
	{
		$infos = $accountManager->getAccountInfo("e4fb8e19-7d5e-40f1-b168-36a3ce2aa23e");
		print_var($infos, "Get Account Informations");
		if(!$infos)
			echo "Erreur : impossible de récupérer les infos du compte :-(\n";
		else
			echo "OK : récupération des infos du compte :-)\n";
	}
		

	// Get All Accounts
	if($action == "gaa")
	{
		$infos = $accountManager->getAllAccounts("zmd.1g6.biz");
		//print_var($infos, "Get All Accounts");
		if(!$infos)
			echo "Erreur : impossible de récupérer la liste des comptes du domaine $nom_domaine :-(\n";
		else
			echo "OK : récupération de la liste des comptes du domaine $nom_domaine :-)\n";
	}

	// Create Account
	if($action == "ca")
	{
		$id = $accountManager->createAccount($nom_compte, 'password');
		print_var($id, "Create Account");
		if(!$id)
			echo "Erreur : compte $nom_compte ($id) pas cr&eacute;&eacute; :-(\n";
		else
			echo "OK : compte $nom_compte ($id) cr&eacute;&eacute; :-)\n";
	}

	// Delete Account
	if($action == "da")
	{
		$id = $accountManager->getAccountId($nom_compte);
		print_var($id, "Delete Account : id");
		$r = $accountManager->deleteAccount($id);
		print_var($r, "Delete Account : Response");
                
		if(!$r)
			echo "Erreur : compte $nom_compte ($id) pas supprim&eacute;\n";
		else
			echo "OK : compte $nom_compte ($id) supprim&eacute;\n";
	}


	// Rename Account
	if($action == "ra")
	{
		$id = $accountManager->createAccount($nom_compte, 'newaccounttest');
                print_var($id, "Rename Account : id");
		$r = $accountManager->renameAccount($id, $new_nom_compte);
                print_var($r, "Rename Account : Response");

		if(!$r)
			echo "Erreur : compte $nom_compte pas renomm&eacute; en $new_nom_compte\n";
		else
			echo "OK : compte $nom_compte renomm&eacute; en $new_nom_compte\n";
	}

	// Add account alias
	if($action == "aaa")
	{
		$aliases = $accountManager->addAccountAlias($nom_compte, "alias_" . $nom_compte);
                print_var($aliases, "Add account alias");
	}

	// Remove account alias
	if($action == "raa")
	{
                $aliases = $accountManager->addAccountAlias($nom_compte, "alias_" . $nom_compte);
		$array = $accoutManager->removeAccountAlias($nom_compte, "alias_" . $nom_compte);
                print_var($array, "Remove account alias");
	}
	






////////////
// Domain //
////////////
$domainManager = new ZmDomain($auth);


	// Get All Domains
	if($action == "gad")
	{
		$infos = $domainManager->getAllDomains();
		print_var($infos);
		if(!$infos)
			echo "Erreur : impossible de récupérer la liste des domaines :-(\n";
		else
			echo "OK : récupération de la liste des domaines :-)\n";
	}

	// Create Domain
	if($action == "cd")
	{
		$id = $domainManager->createDomain($nom_domaine);

		if(!$id)
			echo "Erreur : création domaine $nom_domaine\n";
		else
			echo "OK : création domaine $nom_domaine\n";
	}


	// Delete Domain
	if($action == "dd")
	{
		$id = $domainManager->getDomainId("d197089307.com");               
                //$id = $domainManager->getDomainId("zmd.1g6.biz");
		print_var($id);
		$r = $domainManager->deleteDomain($id);
		print_var($r);
		if(!$r)
			echo "Erreur : suppression domaine $nom_domaine\n";
		else
			echo "OK : suppression domaine $nom_domaine\n";
	}

	// Modify Domain
	if($action == "md")
	{
		$infos = $domainManager->createDomain($nom_domaine);
		print_var($infos);
		$r = $domainManager->modifyDomain($infos['ID'], $infos['A']);

		if(!$r)
			echo "Erreur : modification domaine $nom_domaine\n";
		else
			echo "OK : modification domaine $nom_domaine\n";
	}

////////////
// Server //
////////////
$serverManager = new ZmServer($auth);

	// Get All Server
	if($action == "gs")
	{
		$infos = $serverManager->getAllServers();
		print_var($infos);
		if(!$infos)
			echo "Erreur : impossible de récupérer la liste des comptes du domaine $nom_domaine :-(\n";
		else
			echo "OK : récupération de la liste des comptes du domaine $nom_domaine :-)\n";
	}

	// Create Server
	if($action == "cs")
	{
		$id = $serverManager->createServer($nom_serveur);
		print_var($id);
		if(!$id)
			echo "Erreur : création serveur $nom_serveur\n";
		else
			echo "OK : création serveur $nom_serveur\n";
	}


	// Delete Server
	if($action == "ds")
	{
		$id = $serverManager->getServerId("s260450272.com");
		print_var($id);
		$r = $serverManager->deleteServer($id);
		print_var($r);

		if(!$r)
			echo "Erreur : suppression serveur $nom_serveur\n";
		else
			echo "OK : suppression serveur $nom_serveur\n";
	}

	// Modify Server
	if($action == "ms")
	{
		$infos = $serverManager->createServer($nom_serveur);
		print_var($infos);
		$r = $serverManager->modifyServer($infos['ID'], $infos['A']);

		if(!$r)
			echo "Erreur : modification serveur $nom_serveur\n";
		else
			echo "OK : modification serveur $nom_serveur\n";
	}



?>
