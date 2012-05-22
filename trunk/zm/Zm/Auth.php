<?php

/**
 * Zm_Auth
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
 * Zm_Auth is a class which allows to connect to the Zimbra admin or user space via SOAP
 *
 * Use this class to connect and login to a Zimbra server
 *
 * Example:
 * <code>
 * // either authenticate as admin:
 * $auth = new Zm_Auth($zimbraServer, $zimbraAdminEmail, $zimbraAdminPassword, "admin");
 * // or authenticate as user:
 * $auth = new Zm_Auth($zimbraServer, $userEmail, $userPassword, "user");
 * // then login
 * $l = $auth->login();
 * if(is_a($l, "Exception")) {
 *     echo "Error : cannot login to $zimbraServer\n";
 *     echo $l->getMessage()."\n";
 *     exit();
 * }
 * </code>
 */
class Zm_Auth
{
	/////////////////////
	// Class Variables //
	/////////////////////
	private $client;
	private $soapHeader;
	private $params;
	private $authToken;

	/**
	 * Constructor
	 * @param string $server server name (example: zimbra.yourdomain.com)
	 * @param string $username admin/user account's username
	 * @param string $password admin/user account's password
	 * @param string $authas authenticate as admin or user (default admin)
	 */
	function __construct($server, $username, $password, $authas="admin")
	{
		if ($authas == "admin")
		{
			$location = "https://" . $server . ":7071/service/admin/soap/";
			$uri = "urn:zimbraAdmin";
			$params = array (
					new SoapParam($username, "name"),
					new SoapParam($password, "password"),
			);
		}
		if ($authas == "user")
		{
			$location = "https://" . $server . "/service/soap/";
			$uri = "urn:zimbraAccount";
			$params = array (
					new SoapVar('<account by="name">' . $username . '</account>', XSD_ANYXML),
					new SoapParam($password, "password"),
			);
		}

		$this->client = new SoapClient(null,
		    array(
			'location' => $location,
			'uri' => $uri,
			'trace' => 1,
			'exceptions' => 1,
			'soap_version' => SOAP_1_2,
			'style' => SOAP_RPC,
			'use' => SOAP_LITERAL
		    )
		);

		$this->params = array (
					new SoapVar('<account by="name">' . $username . '</account>', XSD_ANYXML),
					new SoapParam($password, "password")
		);
	}


	function execSoapCall($request, $params = array(), $options = null)
	{
		$result = null;
		$soapHeader = $this->getSoapHeader();

		try
		{
			$soapRes = null;
			$this->client->__soapCall(
					$request,
					$params,
					$options,
					$soapHeader
			);
			$soapRes = $this->client->__getLastResponse();
			//$this->auth->setSoapHeader($soapRes['authToken']);

			$xml = new xml2Array();
			$result = $xml->parse($soapRes);

			//echo htmlentities($result);
			//A tester : $this->objLastResponse = simplexml_load_string($this->_getBodyContent($this->objLastResponseRaw));
		}
		catch (SoapFault $exception)
		{
			// we must re-throw the exception here because this method is only called by the Zm_Account, Zm_Domain, Zm_Server class methods
			throw($exception);
		}

		return $result;
	}

	function getSoapHeader()
	{
		return $this->soapHeader;
	}

	function setSoapHeader($authToken = null)
	{
		if(!$authToken)
		{
			$this->soapHeader = new SoapHeader('urn:zimbra','context');
		}
		else
		{
			$this->soapHeader = array(
				new SoapHeader(
				    'urn:zimbra',
				    'context',
				    new SoapVar('<ns2:context><authToken>' . $authToken . '</authToken></ns2:context>', XSD_ANYXML)
				)
			);
		}
	}

	function getClient()
	{
		return $this->client;
	}

	/**
	 * Use this method to login to a Zimbra server after you create an instance of this class
	 */
	function login()
	{
		$result = null;

		try
		{
			$this->setSoapHeader();

			$result = $this->client->__soapCall("AuthRequest", $this->params, null, $this->getSoapHeader());
			//$result = $this->client->__getLastResponse();
			//print_var($result);

			// Save the soapHeader with token
			$this->setSoapHeader($result['authToken']);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}
}

?>
