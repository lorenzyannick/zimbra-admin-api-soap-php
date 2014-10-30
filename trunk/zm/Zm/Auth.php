<?php

/**
 * Zm_Auth
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @author Fabrizio La Rosa <fabrizio.larosa@unime.it>
 * @version 2.1
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @copyright Copyright (c) 2012, Fabrizio La Rosa
 */
/**
 * Zm_Auth class documentation
 */

// utils.php contains a small collection of useful functions
require_once ("utils.php");

/**
 * Zm_Auth is a class which allows to connect to the Zimbra admin or user space via SOAP
 *
 * Use this class to connect and login to a Zimbra server
 *
 * Example:
 *
 * 	// either authenticate as admin:
 *
 * 	$auth = new Zm_Auth($zimbraServer, $zimbraAdminEmail, $zimbraAdminPassword, "admin");
 *
 * 	// or authenticate as user:
 *
 * 	$auth = new Zm_Auth($zimbraServer, $userEmail, $userPassword, "user");
 *
 * 	// then login
 *
 * 	$l = $auth->login();
 *
 * 	if(is_a($l, "Exception")) {
 *
 *     	echo "Error : cannot login to $zimbraServer\n";
 *
 *     	echo $l->getMessage()."\n";
 *
 *     	exit();
 *
 * 	}
 *
 */
class Zm_Auth
{
	/////////////////////
	// Class Variables //
	/////////////////////
	/**
	 * $auth
	 * @var Zm_Auth $auth soap authentication
	 */
	private $client;
	private $soapHeader;
	private $params;
	private $authToken;
	private $context;
	private $retryAttempts;

	/**
	 * Constructor
	 * @param string $server server name (example: zimbra.yourdomain.com)
	 * @param string $username admin/user account's username
	 * @param string $password admin/user account's password
	 * @param string $authas authenticate as admin or user (default admin)
	 * @param int $attempts how many times we retry to invoke a soapCall (default 3)
	 */
	function __construct($server, $username, $password, $authas="admin", $attempts=3)
	{
		if ($authas == "admin")
		{
			$location = "https://" . $server . ":7071/service/admin/soap/";
			$uri = "urn:zimbraAdmin";
		}
		if ($authas == "user")
		{
			$location = "https://" . $server . "/service/soap/";
			$uri = "urn:zimbraAccount";
		}

		$this->context = $authas;

		$this->client = new SoapClient(null,
		    array(
				'location' => $location,
				'uri' => $uri,
				'trace' => 1,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2,
				'style' => SOAP_RPC,
				'use' => SOAP_LITERAL,
		    )
		);

		$this->params = array (
				new SoapVar('<account by="name">' . $username . '</account>', XSD_ANYXML),
				new SoapParam($password, "password")
		);

		$this->retryAttempts = $attempts;
	}


	/**
	 * @internal
	 */
	function execSoapCall($request, $params = array(), $options = null)
	{
		$result = null;
		$soapHeader = $this->getSoapHeader();
		if ($options["retry"] === false)
			$retry = false;
		else
			$retry = true;
		unset($options["retry"]);

		$n = 0;
		while (true)
		{
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
				break;
			}
			catch (SoapFault $exception)
			{
				// if $retryAttempts>0 retry after a random time using exponential backoff
				// if 'retry' option is false (usually when checking account existence) retries just once
				$n++;
				if ($this->retryAttempts > 0 &&
					$n <= $this->retryAttempts && ($retry || $n == 1) ) {
					$minT = 1+$n*1000000/10;
					$maxT = pow(2, $n-1)*1000000;
					$waitT = rand($minT, $maxT);
					usleep($waitT);
				} else {
					// we must re-throw the exception here because this method is only called by the
					// Zm_Account, Zm_Domain, Zm_Server class methods with their own try ... catch
					throw($exception);
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 */
	function getSoapHeader()
	{
		return $this->soapHeader;
	}

	/**
	 * @internal
	 */
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

	/**
	 * @internal
	 */
	function getClient()
	{
		return $this->client;
	}

	/**
	 * getRetryAttempts
	 * @return int attempts how many times we retry to invoke a soapCall
	 */
	function getRetryAttempts()
	{
		return $this->retryAttempts;
	}

	/**
	 * setRetryAttempts
	 * @param int $attempts how many times we retry to invoke a soapCall
	   the wait time between attempts is progressively increased using an exponential backoff algorithm
	 */
	function setRetryAttempts($attempts)
	{
		if (!$attempts)
			$attempts = 0;
		$this->retryAttempts = $attempts;
	}

	/**
	 * login
	 *
	 * Use this method to login to a Zimbra server after you create an instance of this class
	 *
	 * Login parameters must be specified when calling the constructor
	 */
	function login()
	{
		$result = null;

		$n = 0;
		while (true)
		{
			try
			{
				$this->setSoapHeader();

				$result = $this->client->__soapCall("AuthRequest", $this->params, null, $this->getSoapHeader());
				//$result = $this->client->__getLastResponse();
				//print_var($result);

				// Save the soapHeader with token
				$this->setSoapHeader($result['authToken']);
				break;
			}
			catch (SoapFault $exception)
			{
				// if $retryAttempts>0 retry after a random time using exponential backoff
				// for user logins retries just once
				$n++;
				if ($this->retryAttempts > 0 &&
					$n <= $this->retryAttempts && ($this->context == "admin" || $n == 1) ) {
					$minT = 1+$n*1000000/10;
					$maxT = pow(2, $n-1)*1000000;
					$waitT = rand($minT, $maxT);
					// wait times are shorter on login
					$waitT = $waitT/5;
					usleep($waitT);
				} else {
					$result = $exception;
					break;
				}
			}
		}

		return $result;
	}
}

?>
