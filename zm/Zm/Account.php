<?php

/**
 * Zm_Account
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @author Fabrizio La Rosa <fabrizio.larosa@unime.it>
 * @version 2.0
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @copyright Copyright (c) 2012, Fabrizio La Rosa
 * @package ZimbraSoapPhp
 */

// utils.php contains a small collection of useful functions
require_once ("utils.php");

/**
 * Zm_Account is a class which allows to manage Zimbra accounts via SOAP
 *
 * You may create, modify, rename, delete and get the attributes of a Zimbra account using this class
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
	 * @param string $idOrNameDomain domain id or domain name
	 * @param string $type value of the domain (auto, name, id)
	 * @return array informations
	 */
	function getAllAccounts($idOrNameDomain, $type="auto")
	{
		if($type == "auto")
			$realType = getDomainType($idOrNameDomain);
		else
			$realType = $type;

        $result = null;

		$params = array(
			new SoapVar('<domain by="' . $realType . '">' . $idOrNameDomain . '</domain>', XSD_ANYXML)
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
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
	 * @param array $attrs an optional array to set account options
	 * @return string account id
	 */
	function createAccount($name, $password, $attrs = array())
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name"),
			new SoapParam($password, "password")
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
			new SoapParam($password, "newPassword")
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
	 * @param array $attrs an array to set account options
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
			new SoapParam($accountId, "id")
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
			new SoapParam($newName, "newName")
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
			"zimbraHideInGal"=>$hideInGAL
		);

		return $this->modifyAccount($idOrNameAccount, $attrs, $type);
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
			new SoapVar('<account by="' . $realType . '">' . $idOrNameAccount . '</account>', XSD_ANYXML)
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
		return $this->modifyAccount($idOrNameAccount, $attrs, $type);
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
			new SoapVar('<cos by="name">' . $name . '</cos>', XSD_ANYXML)
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
