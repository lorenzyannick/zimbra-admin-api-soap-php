<?php

/**
 * Zm_Account
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @author Fabrizio La Rosa <fabrizio.larosa@unime.it>
 * @version 2.1
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @copyright Copyright (c) 2012, Fabrizio La Rosa
 * @example ../test.php
 */
/**
 * Zm_Account class documentation
 */

// utils.php contains a small collection of useful functions
require_once ("utils.php");

/**
 * Zm_Account is a class which allows to manage Zimbra accounts via SOAP
 *
 * You may create, modify, rename, delete and get the attributes of a Zimbra account using this class
 *
 * For the usage examples of all class methods check the source code of test.php
 */
class Zm_Account
{
	/**
	 * $auth
	 * @var Zm_Auth $auth soap authentication
	 */
	private $auth;

	/**
	 * Constructor
	 * @param Zm_Auth $auth soap authentication
	 */
	function __construct($auth)
	{
		$this->auth = $auth;
	}


	/**
	 * getAllAccounts
	 * @deprecated it may take a long time to complete and fail on servers with lots of accounts
	 *
	 * use fetchAccounts instead
	 * @param string $idOrNameDomain domain id or domain name
	 * @param string $type value of the domain (auto, name, id)
	 * @return array informations for all accounts
	 */
	function getAllAccounts($idOrNameDomain, $type="auto")
	{
		if($type == "auto")
			$realType = getDomainType($idOrNameDomain);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<domain by="' . $realType . '">' . $idOrNameDomain . '</domain>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAllAccountsRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['GETALLACCOUNTSRESPONSE']['ACCOUNT'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * fetchAccounts
	 * @param string $ldapQuery LDAP-style filter string (RFC 2254)
	 * @param array  $attrList names of requested attributes
	 * @param string $nameDomain domain name to restrict search request
	 * @return array informations for accounts as specified in $ldapQuery
 	 * @author Marc Lamouche <marc.lamouche@ined.fr>
	 */
	function fetchAccounts($ldapQuery, $attrList, $nameDomain = null)
	{
		$result = null;

		$params = array(
			new SoapVar('<query>'.$ldapQuery.'</query>', XSD_ANYXML),
			new SoapParam("accounts", "types"),
			new SoapParam(implode(',', $attrList), "attrs"),
			new SoapParam("0", "limit"),
		);
		if ( is_string($nameDomain) ) $params[] = new SoapParam($nameDomain, "domain");

		try
		{
			$response = $this->auth->execSoapCall(
				"SearchDirectoryRequest",
				$params
			);

			$result = array();

			$resultCount = intval($response['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHDIRECTORYRESPONSE']['SEARCHTOTAL']);
			if ( !$resultCount ) return $result;
			if ( $resultCount > 1 )
				$accountList = &$response['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHDIRECTORYRESPONSE']['ACCOUNT'];
			else
				$accountList = array(&$response['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHDIRECTORYRESPONSE']['ACCOUNT']);

			foreach($accountList as $account)
			{
				$data = array();
				foreach($attrList as $attrName)
					$data[$attrName] = getSoapAttribute($account['A'], $attrName, ATTR_MULTIVALUE);
				$result[] = $data;
				unset($data);
			}
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
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
			new SoapVar('<account by="name">' . $name . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountInfoRequest",
				$params
			);

			$result = getSoapAttribute($result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTINFORESPONSE']['A'], "zimbraId");
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * accountExists
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return bool exists
	 */
	function accountExists($idOrNameAccount, $type="auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML),
		);
		$options = array(
			'retry' => false,
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountInfoRequest",
				$params,
				$options
			);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return (!is_a($result, "Exception"));
	}

	/**
	 * getAccountInfo
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function getAccountInfo($idOrNameAccount, $type="auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML),
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getAccountOption
	 * @param string $idOrNameAccount account id or account name
	 * @param string $optName name of the option to get
	 * @param int $multisingle (ATTR_SINGLEVALUE, ATTR_MULTIVALUE)
	 * @param string $type value of the account (auto, name, id)
	 * @return string option
	 */
	function getAccountOption($idOrNameAccount, $optName, $multisingle=ATTR_SINGLEVALUE, $type="auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountRequest",
				$params
			);

			$result = getSoapAttribute($result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE']['ACCOUNT']['A'], $optName, $multisingle);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getAccountOptions
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array options
	 */
	function getAccountOptions($idOrNameAccount, $type="auto")
	{
		if($type == "auto")
			$realType = getAccountType($idOrNameAccount);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountRequest",
				$params
			);

			$attrs = array();
			foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE']['ACCOUNT']['A'] as $a) {
				$attrs[$a['N']] = $a['DATA'];
			}
			$result = $attrs;
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * createAccount
	 * @param string $name account name
	 * @param string $password password
	 * @param array $attrs an optional array containing the account attributes to be set
	 * @return string the new account's id
	 */
	function createAccount($name, $password, $attrs = array())
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name"),
			new SoapParam($password, "password"),
		);
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"CreateAccountRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATEACCOUNTRESPONSE']['ACCOUNT']['ID'];
			usleep(250000); // introduce a small delay, otherwise some troubles may arise if we modify the new account right after its creation
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * setAccountPassword
	 * @param string $idOrNameAccount account id or account name
	 * @param string $password password
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function setAccountPassword($idOrNameAccount, $password, $type="auto")
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
			new SoapParam($password, "newPassword"),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"SetPasswordRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['SETPASSWORDRESPONSE'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * modifyAccount
	 * @param string $idOrNameAccount account id or account name
	 * @param array $attrs an array containing the account attributes to be set
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function modifyAccount($idOrNameAccount, $attrs = array(), $type="auto")
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
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyAccountRequest",
				$params
			);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * renameAccount
	 * @param string $idOrNameAccount account id or account name
	 * @param string $newName new account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function renameAccount($idOrNameAccount, $newName, $type="auto")
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
			new SoapParam($newName, "newName"),
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * deleteAccount
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function deleteAccount($idOrNameAccount, $type="auto")
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getAccountAliases
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array aliases
	 */
	function getAccountAliases($idOrNameAccount, $type="auto")
	{
		return $this->getAccountOption($idOrNameAccount, "zimbraMailAlias", ATTR_MULTIVALUE, $type);
	}

	/**
	 * addAccountAlias
	 * @param string $idOrNameAccount account id or account name
	 * @param string $alias account alias
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function addAccountAlias($idOrNameAccount, $alias, $type="auto")
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
			new SoapParam($alias, "alias"),
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * removeAccountAlias
	 * @param string $idOrNameAccount account id or account name
	 * @param string $alias account alias
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function removeAccountAlias($idOrNameAccount, $alias, $type="auto")
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
			new SoapParam($alias, "alias"),
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getAccountStatus
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return string status
	 */
	function getAccountStatus($idOrNameAccount, $type="auto")
	{
		return $this->getAccountOption($idOrNameAccount, "zimbraAccountStatus", ATTR_SINGLEVALUE, $type);
	}

	/**
	 * setAccountStatus
	 * @param string $idOrNameAccount account id or account name
	 * @param string $status the status (active, maintenance, pending, locked, closed)
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function setAccountStatus($idOrNameAccount, $status, $type = "auto")
	{
		$hideInGAL = ($status == "active") ? "FALSE" : "TRUE";
		$attrs = array(
			"zimbraAccountStatus"=>$status,
			"zimbraHideInGal"=>$hideInGAL,
		);

		$result = $this->modifyAccount($idOrNameAccount, $attrs, $type);

		return $result;
	}

	/**
	 * expireAccountSessions
	 * @param string $idOrNameAccount account id or account name
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function expireAccountSessions($idOrNameAccount, $type = "auto")
	{
		$attrName = "zimbraAuthTokenValidityValue";
		$oldValue = $this->getAccountOption($idOrNameAccount, $attrName);

		$newValue = rand($oldValue+1, 1024);
		$attrs = array($attrName=>$newValue);

		$result = $this->modifyAccount($idOrNameAccount, $attrs, $type);

		return $result;
	}

	/**
	 * getAccountCos
	 * @param string $idOrNameAccount account id or account name
	 * @param string $returnType get the COS ID or NAME
	 * @param string $type value of the account (auto, name, id)
	 * @return string COS id or name
	 */
	function getAccountCos($idOrNameAccount, $returnType = "NAME", $type = "auto")
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAccountInfoRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTINFORESPONSE']['COS'][$returnType];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * setAccountCos
	 * @param string $idOrNameAccount account id or account name
	 * @param string $cosName the COS name
	 * @param string $type value of the account (auto, name, id)
	 * @return array informations
	 */
	function setAccountCos($idOrNameAccount, $cosName, $type = "auto")
	{
		$cosId = $this->getCosId($cosName);
		$attrs = array("zimbraCOSId"=>$cosId);

		$result = $this->modifyAccount($idOrNameAccount, $attrs, $type);

		return $result;
	}

	/**
	 * getCosId
	 * @param string $name the COS name
	 * @return string COS id
	 */
	function getCosId($name)
	{
		$result = null;

		$params = array(
			new SoapVar('<cos by="name">' . $name . '</cos>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetCosRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['GETCOSRESPONSE']['COS']['ID'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}
}

?>
