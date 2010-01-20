<?php
/**
 * utils.php contient une petite collection de fonction utiles
 */
require_once ("utils.php");

/**
 * Zm_Domain est une classe qui permet de gérer les domaines Zimbra via SOAP
 *
 * Longue description ici, Blablabla ici...
 *
 * @author Yannick Lorenz <ylorenz@1g6.biz>
 * @version 1.0
 * @copyright Copyright (c) 2009, Yannick Lorenz
 * @package Zimbra
 */
class Zm_Domain
{
	/*
         * @var Zm_Auth
         */
	private $auth;


	/**
	 * Constructeur
	 * @param Zm_Auth $auth authentification soap
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
			print_exception($exception);
		}

		return $result;

	}


        /**
	 * getDomainId
	 * @param string $name a domain name
	 * @return string a domain id
	 */
	function getDomainId($name) // don't forget the "virtualHostname" type
	{
		$result = null;

		$params = array(
			new SoapVar('<domain by="name">' . $name . '</domain>', XSD_ANYXML)
		);

		/*
		$soapmessage = $this->soapheader . '<GetDomainRequest xmlns="urn:zimbraAdmin"';
		if ($apply == 0)
			$soapmessage .= ' applyConfig="0"';
		*/


		try
		{
			$result = $this->auth->execSoapCall(
				"GetDomainRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}

		return $result['SOAP:ENVELOPE']['SOAP:BODY']['GETDOMAINRESPONSE']['DOMAIN']['ID'];
	}


	/**
	 * createDomain description
	 * @param string $name a domain name
         * @param array $a an optional array to set domain options
	 * @return array an array with the informations of the created domain
	 */
	function createDomain($name, $a = array())
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
				"CreateDomainRequest", 
				$params
			);
		}
		catch (SoapFault $exception) 
		{
			print_exception($exception);
		}
		
		return $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATEDOMAINRESPONSE']['DOMAIN'];
		//return $result['SOAP:ENVELOPE']['SOAP:BODY']['CREATEDOMAINRESPONSE']['DOMAIN']['ID'];
	}


	/**
	 * deleteDomain description
	 * @param string $idOrNameAccount domain name or domain id
         * @param string $type value of the type (auto, name, id)
	 * @return array informations
	 */
	function deleteDomain($idOrNameDomain, $type = "auto")
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
			print_exception($exception);
		}

		return $result;
	}


	/**
         * modifyDomain
         * @param string $idOrNameDomain domain name or domain id
         * @param string $newName new account name
         * @param string $type value of the type (auto, name, id)
         * @return array
         */
	function modifyDomain($idOrNameDomain, $a = array(), $type = "auto")
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
			new SoapParam($domainId, "id")
		);

		foreach ($a as $key => $value)
		{
			/*
			if($value['N'] == "zimbraId" || $value['N'] == "zimbraDomainName")
				echo '';
			else if($value['N'] == "o")
				$params[] = new SoapVar('<a n="' . $value['N'] . '">NNN' . $value['DATA'] . '</a>', XSD_ANYXML);
			else
			*/
			$params[] = new SoapVar('<a n="' . $value['N'] . '">' . $value['DATA'] . '</a>', XSD_ANYXML);
		}
		try
		{
			$result = $this->auth->execSoapCall(
				"ModifyDomainRequest", 
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
