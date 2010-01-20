<?php
/**
 * utils.php contient une petite collection de fonction utiles
 */
require_once ("utils.php");

/**
 * Zm_Account est une classe qui permet de gérer les comptes Zimbra via SOAP
 *
 * Longue description ici, Blablabla ici...
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @version 1.0
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @package Zimbra
 */
class Zm_Account
{
	/*
         * @var Zm_Auth
         */
	private $auth;


	/**
	 * Constructeur
	 * @param ZmAuth $auth authentification soap
	 */
	function __construct($auth)
	{
		$this->auth = $auth;
	}


	/**
	 * getAllAccounts
	 * @param string $domain domain id or domain name
	 * @param string $type value of the type (auto, name, id)
	 * @return array informations
	 */
	function getAllAccounts($domain, $type = "auto")
	{
		if($type == "auto")
			$realType = getDomainType($domain);
		else
			$realType = $type;

                $result = null;

		$params = array(
			new SoapVar('<domain by="' . $realType . '">' . $domain . '</domain>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAllAccountsRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['GETALLACCOUNTSRESPONSE']['ACCOUNT'];

	}

        /**
	 * getAccountInfo
	 * @param string $str account id or account name
	 * @param string $type value of the type (auto, name, id)
	 * @return array
	 */
	function getAccountInfo($str, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($str);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $str . '</account>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountInfoRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result;
	
	}





	/**
	 * getAccountId
	 * @param string $name account name
	 * @return string account id
	 */
	function getAccountId($name)
	{
		$result = null;

		$params = array(
			new SoapVar('<account by="name">' . $name . '</account>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE']['ACCOUNT']['ID'];
	}


        /**
         * createAccount
         * @param string $name account name
         * @param string $password password
         * @return string account id
         */
	function createAccount($name, $password)
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name"),
			new SoapParam($password, "password")
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"CreateAccountRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATEACCOUNTRESPONSE']['ACCOUNT']['ID'];		
	}

        /**
         * setAccountPassword
         * @param string $idOrNameAccount account name ou account id
         * @param string $password password
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function setAccountPassword($idOrNameAccount, $password, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		if($realType == "name")
			$accountId = $this->getAccountId($idOrNameAccount);
		else
			$accountId = $idOrNameAccount;


		$result = null;

		$params = array(
			new SoapParam($accountId, "id"),
			new SoapParam($password, "newPassword")
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"SetPasswordRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['SETPASSWORDRESPONSE'];

	}


	/**
         * deleteAccount
         * @param string $idOrNameAccount account name ou account id
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function deleteAccount($idOrNameAccount, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		if($realType == "name")
			$accountId = $this->getAccountId($idOrNameAccount);
		else
			$accountId = $idOrNameAccount;


		$result = null;

		$params = array( 
			new SoapParam($accountId, "id"), 
		);


		try
		{
			$result = $this->auth->execSoapCall(
				"DeleteAccountRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{         
			print_exception($exception);
		}

		return $result; 
	}

        /**
         * renameAccount
         * @param string $idOrNameAccount account name ou account id
         * @param string $newName new account name
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function renameAccount($idOrNameAccount, $newName, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		if($realType == "name")
			$accountId = $this->getAccountId($idOrNameAccount);
		else
			$accountId = $idOrNameAccount;

		$result = null;

		$params = array( 
			new SoapParam($accountId, "id"),
			new SoapParam($newName, "newname")
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"RenameAccountRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{         
			print_exception($exception);
		}

		return $result; 
	}

        /**
         * addAccountAlias
         * @param string $idOrNameAccount account name ou account id
         * @param string $alias account alias
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function addAccountAlias($idOrNameAccount, $alias, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		if($realType == "name")
			$accountId = $this->getAccountId($idOrNameAccount);
		else
			$accountId = $idOrNameAccount;

		$result = null;

		$params = array( 
			new SoapParam($accountId, "id"),
			new SoapParam($alias, "alias")
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"AddAccountAliasRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{         
			print_exception($exception);
		}

		return $result; 


	}

        /**
         * removeAccountAlias
         * @param string $idOrNameAccount account name ou account id
         * @param string $alias account alias
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function removeAccountAlias($idOrNameAccount, $alias, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		if($realType == "name")
			$accountId = $this->getAccountId($idOrNameAccount);
		else
			$accountId = $idOrNameAccount;

		$result = null;

		$params = array( 
			new SoapParam($accountId, "id"),
			new SoapParam($alias, "alias")
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"RemoveAccountAliasRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{         
			print_exception($exception);
		}

		return $result; 

	}

        /**
         * getAccountAliases
         * @param string $idOrNameAccount account name ou account id
         * @param string $type value of the type (auto, name, id)
         * @return array alias
         */
	function getAccountAliases($idOrNameAccount, $type = "auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

                $aliases = array ();
		foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE']['ACCOUNT']['A'] as $anAlias) {
			if ($anAlias['N'] == "zimbraMailAlias"){
				$aliases[] = $anAlias['DATA'];				
			}
		}
		return $aliases;
	}



}

?>
