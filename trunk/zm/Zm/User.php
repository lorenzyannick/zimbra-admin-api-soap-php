<?php

/**
 * Zm_User
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
 * Zm_User is a class which allows a Zimbra user to manage its own account via SOAP
 *
 * You may change password, modify and get the preferences of a Zimbra user using this class
 *
 * For the usage examples of all class methods check the source code of testuser.php
 */
class Zm_User
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
	 * userExists
	 * @param string $userName user name
	 * @return bool exists
	 */
	function userExists($userName)
	{
		$result = null;

		$params = array(
			new SoapVar('<account by="name">' . $userName . '</account>', XSD_ANYXML),
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
	 * getUserInfo
	 * @param string $userName user name
	 * @return array informations
	 */
	function getUserInfo($userName)
	{
		$result = null;

		$params = array(
			new SoapVar('<account by="name">' . $userName . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetInfoRequest",
				$params
			);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['GETINFORESPONSE'];
	}

	/**
	 * getUserAttrs
	 * @param string $userName user name
	 * @return array attributes
	 */
	function getUserAttrs($userName)
	{
		$result = null;

		$params = array(
 			new SoapVar('<account by="name">' . $userName . '</account>', XSD_ANYXML),
			new SoapParam("attrs", "sections"),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetInfoRequest",
				$params
			);

			$attrs = array();
			foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETINFORESPONSE']['ATTRS']['ATTR'] as $a) {
				$attrs[$a['NAME']] = $a['DATA'];
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
	 * getUserPrefs
	 * @param string $userName user name
	 * @return array prefs
	 */
	function getUserPrefs($userName)
	{
		$result = null;

		$params = array(
			new SoapVar('<account by="name">' . $userName . '</account>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetPrefsRequest",
				$params
			);

			$prefs = array();
			foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETPREFSRESPONSE']['PREF'] as $p) {
				$prefs[$p['NAME']] = $p['DATA'];
			}
			$result = $prefs;
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * changeUserPassword
	 * @param string $userName user name
	 * @param string $oldPassword old password
	 * @param string $newPassword new password
	 * @return array informations
	 */
	function changeUserPassword($userName, $oldPassword, $newPassword)
	{
		$result = null;

		$params = array(
			new SoapParam($userName, "account"),
			new SoapParam($oldPassword, "oldPassword"),
			new SoapParam($newPassword, "password"),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"ChangePasswordRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['CHANGEPASSWORDRESPONSE'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * modifyUserPrefs
	 * @param string $userName user name
	 * @param array $prefs an array containing the user prefs to be set
	 * @return array informations
	 */
	function modifyUserPrefs($userName, $prefs = array())
	{
		$result = null;

		$params = array(
			new SoapParam($userName, "account"),
		);
		foreach ($prefs as $key=>$value)
			$params[] = new SoapVar('<pref name="' . $key . '">' . $value . '</pref>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyPrefsRequest",
				$params
			);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}
}

?>
