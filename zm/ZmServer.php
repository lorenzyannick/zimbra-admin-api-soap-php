<?php
/**
 * utils.php contient une petite collection de fonction utiles
 */
require_once ("utils.php");

/**
 * ZmServer est une classe qui permet de gérer les serveurs Zimbra via SOAP
 *
 * Longue description ici, Blablabla ici...
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @version 1.0
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @package Zimbra
 */
class ZmServer
{
	/*
         * @var ZmAuth
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
	 * getAllServers
	 * @return array informations
	 */
	function getAllServers()
	{
		$result = null;

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAllServersRequest"
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result;

	}


	/**
	 * getServerId
	 * @param string $name a domain name
         * @param string $type value of the type (auto, name, id)
	 * @return string a domain id
	 */
	function getServerId($str, $type = "auto") // don't forget serviceHostname
	{
                if($type == "auto")
			$realType = getServerType($str);
		else
			$realType = $type;

                $result = null;
                
		$params = array(
			new SoapVar('<server by="' . $realType . '">' . $str . '</server>', XSD_ANYXML)
		);

		/*
		$soapmessage = $this->soapheader . '<GetServerRequest xmlns="urn:zimbraAdmin"';
		if ($apply != "")
			$soapmessage .= ' applyConfig="' . $apply . '"';
		*/

		try
		{
			$result = $this->auth->execSoapCall(
				"GetServerRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

                return $result['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVERRESPONSE']['SERVER']['ID'];
	}


	/**
	 * createServer description
	 * @param string $name a server name
         * @param array $a an optional array to set server options
	 * @return array an array with the informations of the created server
	 */
	function createServer($name, $a = array ())
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name")
		);

		foreach ($a as $key => $value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"CreateServerRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATESERVERRESPONSE']['SERVER'];	
	}


	/**
	 * deleteServer description
	 * @param string $idOrNameServer server name or server id
         * @param string $type value of the type (auto, name, id)
	 * @return array informations
	 */
	function deleteServer($idOrNameServer, $type = "auto")
	{
		if($type == "auto")
			$realType = getServerType($idOrNameServer);
		else
			$realType = $type;

		if($realType == "name")
			$serverId = $this->getServerId($idOrNameServer);
		else
			$serverId = $idOrNameServer;


		$result = null;

		$params = array( 
			new SoapParam($serverId, "id"), 
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"DeleteServerRequest", 
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
         * modifyServer description
         * @param string $idOrNameServer server name or server id
         * @param array $a an optional array to set server options
         * @param string $type value of the type (auto, name, id)
         * @return array informations
         */
	function modifyServer($idOrNameServer, $a = array(), $type = "auto")
	{
		if($type == "auto")
			$realType = getServerType($idOrNameServer);
		else
			$realType = $type;

		if($realType == "name")
			$serverId = $this->getServerId($idOrNameServer);
		else
			$serverId = $idOrNameServer;


		$result = null;

		$params = array( 
			new SoapParam($serverId, "id")
		);
		
		foreach ($a as $key => $value)
			$params[] = new SoapVar('<a n="' . $value['N'] . '">' . $value['DATA'] . '</a>', XSD_ANYXML);
		
		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyServerRequest", 
				$params
			);
		}
		catch (SoapFault $exception)
		{         
			print_exception($exception);
		}

		return $result; 
	}

}

?>
