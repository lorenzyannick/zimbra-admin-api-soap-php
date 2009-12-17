<?php
/**
 * utils.php contient une petite collection de fonction utiles
 */
require_once ("utils.php");

/**
 * ZmAuth est une classe qui permet de se connecter à l'espace admin de Zimbra via SOAP
 *
 * Longue description ici, Blablabla ici...
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @version 1.0
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @package Zimbra
 */
class ZmAuth
{
	//////////////////////////
	// Variables de classes //
	//////////////////////////
	private $client;
	private $soapHeader;
	private $params;
	private $authToken;

	function execSoapCall($request, $params = array(), $options = null)
	{
		$soapHeader = $this->getSoapHeader();

		$result = null;
		try
		{
			$this->client->__soapCall(
					$request, 
					$params, 
					$options,
					$soapHeader
			);
			$result = $this->client->__getLastResponse();
			//$this->auth->setSoapHeader($result['authToken']);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

                
                $xml = new xml2Array();
		$res = $xml->parse($result);

		//echo htmlentities($result);
		//A tester : $this->objLastResponse = simplexml_load_string($this->_getBodyContent($this->objLastResponseRaw));


		return $res;
              
	}

	/**
	 * Constructeur
	 * @param string $server nom du serveur (exemple : zmd.1g6.biz)
         * @param string $username login du compte admin
         * @param string $password mot de passe du compte admin
	 */
	function __construct($server, $username, $password)
	{
		$this->client = new SoapClient(null,
		    array(
			'location' => "https://" . $server . ":7071/service/admin/soap/",
			'uri' => "urn:zimbraAdmin",
			'trace' => 1,
			'exceptions' => 1,
			'soap_version' => SOAP_1_2,
			'style' => SOAP_RPC,
			'use' => SOAP_LITERAL
		    )
		);

		$this->params = array (
					new SoapParam($username, "name"),
					new SoapParam($password, "password")
		);

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
			print_exception($exception);
		}
		
		return $result;
	}
    
} 


?>
