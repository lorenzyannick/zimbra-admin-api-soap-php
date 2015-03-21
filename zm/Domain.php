<?php

/**
 * Zm_Domain
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @author Fabrizio La Rosa <fabrizio.larosa@unime.it>
 * @version 2.1
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @copyright Copyright (c) 2012, Fabrizio La Rosa
 * @example ../test.php
 */
/**
 * Zm_Domain class documentation
 */

// utils.php contains a small collection of useful functions
require_once ("utils.php");

/**
 * Zm_Domain is a class which allows to manage Zimbra domains via SOAP
 *
 * You may create, modify, rename, delete and get the attributes of a Zimbra domain using this class
 *
 * For the usage examples of all class methods check the source code of test.php
 */
class Zm_Domain
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
	 * getAllDomains
	 * @return array informations
	 */
	function getAllDomains()
	{
		$result = null;

		try
		{
			$result = $this->auth->execSoapCall(
				"GetAllDomainsRequest"
			);
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * getDomainId
	 * @param string $name a domain name
	 * @return string a domain id
	 */
	function getDomainId($name)
	{
		$result = null;

		$params = array(
			new SoapVar('<domain by="name">' . $name . '</domain>', XSD_ANYXML),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"GetDomainInfoRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['GETDOMAININFORESPONSE']['DOMAIN']['ID'];
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * domainExists
	 * @param string $idOrNameDomain domain id or domain name
	 * @param string $type value of the domain (auto, name, id)
	 * @return bool exists
	 */
	function domainExists($idOrNameDomain, $type="auto")
	{
		if($type == "auto")
			$realType = getDomainType($idOrNameDomain);
		else
			$realType = $type;

		if($realType == "name")
			$domainId = $this->getDomainId($idOrNameDomain);
		else
			$domainId = $idOrNameDomain;

		$result = $this->getDomainId($domainId);

		return (!stristr($result, "dummy"));
	}

	/**
	 * getDomainOptions
	 * @param string $idOrNameDomain domain id or domain name
	 * @param string $type value of the domain (auto, name, id)
	 * @return array
	 */
	function getDomainOptions($idOrNameDomain, $type="auto")
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
				"GetDomainRequest",
				$params
			);

			$attrs = array();
			foreach ($result['SOAP:ENVELOPE']['SOAP:BODY']['GETDOMAINRESPONSE']['DOMAIN']['A'] as $a) {
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
	 * createDomain
	 * @param string $name a domain name
	 * @param array $attrs an optional array containing the domain attributes to be set
	 * @return array an array with the new domain's info
	 */
	function createDomain($name, $attrs = array())
	{
		$result = null;

		$params = array(
			new SoapParam($name, "name"),
		);
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"CreateDomainRequest",
				$params
			);

			$result = $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATEDOMAINRESPONSE']['DOMAIN'];
			usleep(250000); // introduce a small delay, otherwise some troubles may arise if we modify the new domain right after its creation
		}
		catch (SoapFault $exception)
		{
			$result = $exception;
		}

		return $result;
	}

	/**
	 * modifyDomain
	 * @param string $idOrNameDomain domain id or domain name
	 * @param array $attrs an array containing the domain attributes to be set
	 * @param string $type value of the domain (auto, name, id)
	 * @return array
	 */
	function modifyDomain($idOrNameDomain, $attrs = array(), $type="auto")
	{
		if($type == "auto")
			$realType = getDomainType($idOrNameDomain);
		else
			$realType = $type;

		if($realType == "name")
			$domainId = $this->getDomainId($idOrNameDomain);
		else
			$domainId = $idOrNameDomain;

		$result = null;

		$params = array(
			new SoapParam($domainId, "id"),
		);
		foreach ($attrs as $key=>$value)
			$params[] = new SoapVar('<a n="' . $key . '">' . $value . '</a>', XSD_ANYXML);

		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyDomainRequest",
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
	 * deleteDomain
	 * @param string $idOrNameDomain domain id or domain name
	 * @param string $type value of the domain (auto, name, id)
	 * @return array informations
	 */
	function deleteDomain($idOrNameDomain, $type="auto")
	{
		if($type == "auto")
			$realType = getDomainType($idOrNameDomain);
		else
			$realType = $type;

		if($realType == "name")
			$domainId = $this->getDomainId($idOrNameDomain);
		else
			$domainId = $idOrNameDomain;

		$result = null;

		$params = array(
			new SoapParam($domainId, "id"),
		);

		try
		{
			$result = $this->auth->execSoapCall(
				"DeleteDomainRequest",
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
