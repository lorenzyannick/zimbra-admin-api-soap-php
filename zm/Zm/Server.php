<?php

/**
 * Zm_Server
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
 * Zm_Server is a class which allows to manage Zimbra servers via SOAP
 *
 * You may create, modify, rename, delete and get the attributes of a Zimbra server using this class
 */
class Zm_Server
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
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getServerId
	 * @param string $name a server name
	 * @return string a server id
	 */
	function getServerId($name)
	{
		$result = null;

		$params = array(
			new SoapVar('<server by="name">' . $name . '</server>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetServerRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVERRESPONSE']['SERVER']['ID'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * serverExists
	 * @param string $idOrNameServer server id or server name
	 * @param string $type value of the server (auto, name, id)
	 * @return bool exists
	 */
	function serverExists($idOrNameServer, $type="auto")
	{
		if($type == "auto")
			$realType = getServerType($idOrNameServer);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<server by="' . $realType . '">' . $idOrNameServer . '</server>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetServerRequest",
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
	 * getServerOptions
	 * @param string $idOrNameServer server id or server name
	 * @param string $type value of the server (auto, name, id)
	 * @return array
	 */
	function getServerOptions($idOrNameServer, $type="auto")
	{
		if($type == "auto")
			$realType = getServerType($idOrNameServer);
		else
			$realType = $type;

		$result = null;

		$params = array(
			new SoapVar('<server by="' . $realType . '">' . $idOrNameServer . '</server>', XSD_ANYXML)
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetServerRequest",
				$params
			);

			$attrs = array();
			foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVERRESPONSE']['SERVER']['A'] as $a) {
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
	 * createServer
	 * @param string $name a server name
	 * @param array $attrs an optional array to set server options
	 * @return array an array with the informations of the created server
	 */
	function createServer($name, $attrs = array ())
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name")
		);
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"CreateServerRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATESERVERRESPONSE']['SERVER'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * modifyServer
	 * @param string $idOrNameServer server id or server name
	 * @param array $attrs an array to set server options
	 * @param string $type value of the server (auto, name, id)
	 * @return array informations
	 */
	function modifyServer($idOrNameServer, $attrs = array(), $type="auto")
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
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyServerRequest",
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
	 * deleteServer
	 * @param string $idOrNameServer server id or server name
	 * @param string $type value of the server (auto, name, id)
	 * @return array informations
	 */
	function deleteServer($idOrNameServer, $type="auto")
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
			$result = $exception;
		}

		return $result;
	}
}

?>
