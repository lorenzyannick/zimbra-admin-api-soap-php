<?php
require_once ("xmlparse.php");
/**
 * Version 0.1 grosse modif par tl@celeste.fr
 * origins
 * ftp://ftp.rongage.org/pub/zimbraAdmin/
 */
class zimbraAdmin {
	var $soapheader;
	var $zimbra_error;
	var $zimbra_errno;
	var $zimbra_session;
	var $zimbra_auth;
	var $curlhandle;

	function zimbraAdmin($server) {

		$this->curlhandle = curl_init();
		curl_setopt($this->curlhandle, CURLOPT_URL, "https://$server:7071/service/admin/soap");
		curl_setopt($this->curlhandle, CURLOPT_POST, TRUE);
		curl_setopt($this->curlhandle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->curlhandle, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->curlhandle, CURLOPT_SSL_VERIFYHOST, FALSE);

	}

	function set_zimbra_header() {
		$this->soapheader = '<soap:Envelope
	xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
	<soap:Header>
	  <context xmlns="urn:zimbra"';
		if ($this->zimbra_session != 0)
			$this->soapheader .= '>
		    <authToken>' . $this->zimbra_auth . '</authToken>
		    <sessionId id="' . $this->zimbra_session . '">' . $this->zimbra_session . '</sessionId>
		  </context>';
		else
			$this->soapheader .= '/>';
		$this->soapheader .= ' 
	</soap:Header>
	<soap:Body>
	';
	}

	function zimbra_login($adminuser, $adminpass) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<AuthRequest xmlns="urn:zimbraAdmin">
	  <name>' . $adminuser . '</name>
	  <password>' . $adminpass . '</password>
	</AuthRequest>
	</soap:Body>
	</soap:Envelope>';


		//echo "\n\n\nSOAP : <XMP>$soapmessage</XMP>XXXXXXX\n\n\n";

		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			//echo "$this->zimbraerror\n$this->zimbraerrno\n\n";
			return false;
			
		}

		$res = $xml->parse($zimbraSOAPResponse);

		//echo "\n\n\nYYYYYYYYYY\n\n\n";
		//print_r ($res);
		//echo "\n\n\nYYYYYYYYYY\n\n\n";

		if (!isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['AUTHRESPONSE']))
			return false;
		$x = $res['SOAP:ENVELOPE']['SOAP:BODY']['AUTHRESPONSE'];
		//$this->zimbra_session = $x['SESSIONID']['DATA'];
		$this->zimbra_session = 1; // ne sert à rien !
		$this->zimbra_auth = $x['AUTHTOKEN']['DATA'];

		return true;
	}

	function zimbra_delegate_auth($account, $accounttype = "name", $duration = 0) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<DelegateAuthRequest xmlns="urn:zimbraAdmin"';
		if ($duration > 0)
			$soapmessage .= ' duration="' . $duration;
		$soapmessage .= '>
	  <account by="' . $accounttype . '>' . $account . '</account>
	</DelegateAuthRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (!isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DELEGATEAUTHRESPONSE']))
			return false;
		$x = $res['SOAP:ENVELOPE']['SOAP:BODY']['DELEGATEAUTHRESPONSE'];
		$this->zimbra_session = $x['SESSIONID']['DATA'];
		$this->zimbra_auth = $x['AUTHTOKEN']['DATA'];

		return true;
	}

	function zimbra_get_all_admin_accounts() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<GetAllAdminAccountsRequest xmlns="urn:zimbraAdmin">
	</GetAllAdminAccountsRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLADMINACCOUNTSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLADMINACCOUNTSRESPONSE'];
		return true;
	}

	function zimbra_get_all_accounts($domain, $by = "name") {
		
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<GetAllAccountsRequest xmlns="urn:zimbraAdmin">
		<domain by="' . $by . '">' . $domain . '</domain>
	</GetAllAccountsRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		//print_r($soapmessage);echo "?";
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
	
		$res = $xml->parse($zimbraSOAPResponse);
		
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLACCOUNTSRESPONSE'])){
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLACCOUNTSRESPONSE'];
	
		}
			//CAS retour domaine vide ou non  présent
		
		
		 if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['SOAP:FAULT']['SOAP:DETAIL']['ERROR'])){
		 //	print_r($res['SOAP:ENVELOPE']['SOAP:BODY']['SOAP:FAULT']['SOAP:DETAIL']['ERROR']['CODE']['DATA']);
		 	
		 	return $res['SOAP:ENVELOPE']['SOAP:BODY']['SOAP:FAULT']['SOAP:DETAIL']['ERROR']['CODE']['DATA'];
		 }
		return true;
		
	}

	function zimbra_set_account_password($name, $password) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_account($name);
		$soapmessage = $this->soapheader . '
	<SetPasswordRequest xmlns="urn:zimbraAdmin">
	<id>' . $id . '</id>
	<newPassword>' . $password . '</newPassword>
	</SetPasswordRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['NEWPASSWORDRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['NEWPASSWORDRESPONSE'];
		return false;
	}

	function zimbra_add_account_alias($name, $alias) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_account($name);
		$soapmessage = $this->soapheader . '
	<AddAccountAliasRequest xmlns="urn:zimbraAdmin">
	<id>' . $id . '</id>
	<alias>' . $alias . '</alias>
	</AddAccountAliasRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['ADDACCOUNTALIASRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['ADDACCOUNTALIASRESPONSE'];
		return false;
	}

	function zimbra_remove_account_alias($name, $alias) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_account($name);
		$soapmessage = $this->soapheader . '
	<RemoveAccountAliasRequest xmlns="urn:zimbraAdmin">
	<id>' . $id . '</id>
	<alias>' . $alias . '</alias>
	</RemoveAccountAliasRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		
		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['REMOVEACCOUNTALIASRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['REMOVEACCOUNTALIASRESPONSE'];
		return false;
	}

	function zimbra_create_account($name, $password, $a = array ()) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<CreateAccountRequest xmlns="urn:zimbraAdmin">
	<name>' . $name . '</name>
	<password>' . $password . '</password>';
		foreach ($a as $key => $value)
			$soapmessage .= '
		<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
	</CreateAccountRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		$res = $xml->parse($zimbraSOAPResponse);

		//echo "\n\n\n1CCCCCCCCCCCCC\n\n\n";
		//print_r ($res);
		//echo "\n\n\n2CCCCCCCCCCCCC\n\n\n";

		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['CREATEACCOUNTRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['CREATEACCOUNTRESPONSE'];
		return false;
	}

	function zimbra_get_account($name, $type = "name", $apply = 1) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<GetAccountRequest xmlns="urn:zimbraAdmin"';
		if ($apply == 0)
			$soapmessage .= ' applyCos="0"';
		$soapmessage .= '>
	<account by="' . $type . '">' . $name . '</account>
	</GetAccountRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTRESPONSE'];
		return false;
	}

	function zimbra_get_account_info($name, $type = "name") {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<GetAccountInfoRequest xmlns="urn:zimbraAdmin">
	<account by="' . $type . '">' . $name . '</account>
	</GetAccountInfoRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTINFORESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTINFORESPONSE'];
		return false;
	}

	function zimbra_get_account_membership($name, $type = "name") {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<GetAccountMembershipRequest xmlns="urn:zimbraAdmin">
	<account by="' . $type . '">' . $name . '</account>
	</GetAccountMembershipRequest>
	</soap:Body>
	</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTMEMBERSHIPRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETACCOUNTMEMBERSHIPRESPONSE'];
		return false;
	}

	function zimbra_modify_account($name, $a) {
		$id = $this->zimbra_get_id_from_account($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
	<ModifyAccountRequest xmlns="urn:zimbraAdmin">
	<id>' . $id . '</id>';
		foreach ($a as $key => $value)
			$soapmessage .= '
		<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
	</ModifyAccountRequest>
	</soap:Body>
	</soap:Envelope>';
		
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		
		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYACCOUNTRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYACCOUNTRESPONSE'];
		return false;
	}

	function zimbra_rename_account($name, $newname) {
		$id = $this->zimbra_get_id_from_domain($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<RenameAccountRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		<newName>' . $newname . '</newname>
		</RenameAccountRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['RENAMEACCOUNTRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['RENAMEACCOUNTRESPONSE'];
		return false;
	}

	function zimbra_check_password_strength($name, $password) {
		$id = $this->zimbra_get_id_from_domain($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<CheckPasswordStrengthRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		<password>' . $password . '</password>
		</CheckPasswordStrengthRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['CHECKPASSWORDSTRENGTHRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['CHECKPASSWORDSTRENGTHRESPONSE'];
		return false;
	}

	function zimbra_delete_account($name) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_account($name);
		$soapmessage = $this->soapheader . '
		<DeleteAccountRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		</DeleteAccountRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DELETEACCOUNTRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['DELETEACCOUNTRESPONSE'];
		return false;
	}

	function zimbra_auto_complete_gal($domain, $name, $limit = 0, $type = "account") {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<AutoCompleteGalRequest xmlns="urn:zimbraAdmin" domain=\"$domain\" type=\"$type\" limit=\"$limit\">
		<name>' . $name . '</name>
		</AutoCompleteGalRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['AUTOCOMPLETEGALRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['AUTOCOMPLETEGALRESPONSE'];
		return false;
	}
/**
 * 
 */
	function zimbra_search_gal($name,$domain,$type = "all") {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<SearchGalRequest xmlns="urn:zimbraAdmin" domain="'.$domain.'" type="'.$type.'">
		<name>' . $name . '</name>
		</SearchGalRequest>
		</soap:Body>
		</soap:Envelope>';

		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHGALRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHGALRESPONSE'];
		return false;
	}
	
	function zimbra_get_quota_usage($limit = 0, $offset = 0, $domain, $sort = "", $sortAsc = "") {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetQuotaUsageRequest xmlns="urn:zimbraAdmin"';
		if ($limit != 0)
			$soapmessage .= ' limit=\"'.$limit.'\"';
		if ($offset != 0)
			$soapmessage .= ' offset=\"$offset\"';
			
		$soapmessage .= ' domain="'.$domain.'"';
		if ($sort != "")
			$soapmessage .= ' sortBy=\"$sort\"';
		if ($sortAsc != "")
			$soapmessage .= ' sortAscending=\"$sortAsc\"';
		$soapmessage .= '>' .
				'
		</GetQuotaUsageRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETQUOTAUSAGERESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETQUOTAUSAGERESPONSE'];
		return false;
	}
	/**
	 * it works
	 */
	function zimbra_get_all_quota_usage() {
		$limit = 0; $offset = 0;$sort = ""; $sortAsc = "";
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetQuotaUsageRequest xmlns="urn:zimbraAdmin"';
		if ($limit != 0)
			$soapmessage .= ' limit=\"'.$limit.'\"';
		if ($offset != 0)
			$soapmessage .= ' offset=\"$offset\"';
		if ($sort != "")
			$soapmessage .= ' sortBy=\"$sort\"';
		if ($sortAsc != "")
			$soapmessage .= ' sortAscending=\"$sortAsc\"';
		$soapmessage .= '>
		</GetQuotaUsageRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETQUOTAUSAGERESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETQUOTAUSAGERESPONSE'];
		return false;
	}
	
	function zimbra_get_quota_usage_once($limit = 0, $offset = 0, $domain = "", $sort = "", $sortAsc = ""){
				$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetQuotaUsageRequest xmlns="urn:zimbraAdmin"';
	//	if ($limit != 0)
	//			$soapmessage .= ' limit=\"'.$limit.'\"';
	//	if ($offset != 0)
	//		$soapmessage .= ' offset=\"$offset\"';
	/*		if ($domain != "")
			$soapmessage .= ' domain=\"acadomia.fr\"';
		if ($sort != "")
			$soapmessage .= ' sortBy=\"$sort\"';
		if ($sortAsc != "")
			$soapmessage .= ' sortAscending=\"$sortAsc\"';*/
		$soapmessage .= '>' .
	//					'<account name="jeff.hildebrand@acadomia.fr" used="quota-used" limit="quota-limit"	/>'.	
		'</GetQuotaUsageRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		//print("reponse=> ".$zimbraSOAPResponse);
		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHGALRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHGALRESPONSE'];
		return false;
	}
	function zimbra_create_cos($name, $a = array ()) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<CreateCosRequest xmlns="urn:zimbraAdmin">
		<name>' . $name . '</name>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</CreateCosRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['CREATECOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['CREATECOSRESPONSE'];
		return false;
	}

	function zimbra_get_all_cos() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetAllCosRequest xmlns="urn:zimbraAdmin">
		</GetAllCosRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLCOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLCOSRESPONSE'];
		return false;
	}
	/**
	 * type can be id or name	GROS BUG CA RACE!
	 * http://bugzilla.zimbra.com/show_bug.cgi?id=31593
	 */
	function zimbra_get_cos($name,$domain,$type = "name") {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetCosRequest xmlns="urn:zimbraAdmin" domain="'.$domain.'">
			<cos by="' . $type . '">' . $name . '</cos>
		</GetCosRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}
		
		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETCOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETCOSRESPONSE'];
		return false;
	}
	

	function zimbra_modify_cos($name, $a) {
		$id = $this->zimbra_get_id_from_cos($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<ModifyCosRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</ModifyCosRequest>
		</soap:Body>
		</soap:Envelope>';

		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYCOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYCOSRESPONSE'];
		return false;
	}

	function zimbra_rename_cos($name, $newname) {
		$id = $this->zimbra_get_id_from_cos($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<RenameCosRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		<newName>' . $newname . '</newName>
		</ModifyCosRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['RENAMECOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['RENAMECOSRESPONSE'];
		return false;
	}

	function zimbra_delete_cos($name) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_cos($name);
		$soapmessage = $this->soapheader . '
		<DeleteCosRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		</DeleteCosRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DELETECOSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['DELETECOSRESPONSE'];
		return false;
	}

	function zimbra_create_server($name, $a = array ()) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<CreateServerRequest xmlns="urn:zimbraAdmin">
		<name>' . $name . '</name>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</CreateServerRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['CREATESERVERRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['CREATESERVERRESPONSE'];
		return false;
	}

	function zimbra_get_all_servers() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetAllServersRequest xmlns="urn:zimbraAdmin">
		</GetAllServersRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLSERVERSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLSERVERSRESPONSE'];
		return false;
	}

	function zimbra_get_server($name, $type = "name", $apply = "") {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetServerRequest xmlns="urn:zimbraAdmin"';
		if ($apply != "")
			$soapmessage .= ' applyConfig="' . $apply . '"';
		$soapmessage .= '>
		<server by="' . $type . '">' . $name . '</cos>
		</GetServerRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVERRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVERRESPONSE'];
		return false;
	}

	function zimbra_modify_server($name, $a) {
		$id = $this->zimbra_get_id_from_server($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<ModifyServerRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</ModifyServerRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYSERVERRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYSERVERRESPONSE'];
		return false;
	}

	function zimbra_delete_server($name) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$id = $this->zimbra_get_id_from_server($name);
		$soapmessage = $this->soapheader . '
		<DeleteServerRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		</DeleteServerRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DELETESERVERRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['DELETESERVERRESPONSE'];
		return false;
	}

	function zimbra_get_all_domains() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetAllDomainsRequest xmlns="urn:zimbraAdmin">
		</GetAllDomainsRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLDOMAINSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLDOMAINSRESPONSE'];
		return false;
	}

	function zimbra_create_domain($domain, $a = array ()) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<CreateDomainRequest xmlns="urn:zimbraAdmin">
		<name>' . $domain . '</name>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</CreateDomainRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['CREATEDOMAINRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['CREATEDOMAINRESPONSE'];
		return false;
	}

	function zimbra_get_domain($domain, $type = "name", $apply = 1) {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetDomainRequest xmlns="urn:zimbraAdmin"';
		if ($apply == 0)
			$soapmessage .= ' applyConfig="0"';
		$soapmessage .= '>
		<domain by="' . $type . '">' . $domain . '</domain>
		</GetDomainRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETDOMAINRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETDOMAINRESPONSE'];
		return false;
	}

	function zimbra_modify_domain($name, $a) {
		$id = $this->zimbra_get_id_from_domain($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<ModifyDomainRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</ModifyDomainRequest>
		</soap:Body>
		</soap:Envelope>';
		#print_r ($soapmessage);
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYDOMAINRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYDOMAINRESPONSE'];
		return false;
	}

	function zimbra_delete_domain($id) {
		$id = $this->zimbra_get_id_from_domain($name);
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<DeleteDomainRequest xmlns="urn:zimbraAdmin">
		<id>' . $id . '</id>
		</DeleteDomainRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DELETEDOMAINRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['DELETEDOMAINRESPONSE'];
		return false;
	}

	function zimbra_get_config($a) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetConfigRequest xmlns="urn:zimbraAdmin">';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</GetConfigRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETCONFIGRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETCONFIGRESPONSE'];
		return false;
	}

	function zimbra_modify_config($a) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<ModifyConfigRequest xmlns="urn:zimbraAdmin">';
		foreach ($a as $key => $value)
			$soapmessage .= '
			<a n="' . $key . '">' . $value . '</a>';
		$soapmessage .= '
		</ModifyConfigRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYCONFIGRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MODIFYCONFIGRESPONSE'];
		return false;
	}

	function zimbra_get_all_config() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetAllConfigRequest xmlns="urn:zimbraAdmin">
		</GetAllConfigRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLCONFIGRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLCONFIGRESPONSE'];
		return false;
	}

	function zimbra_search_directory_request($query, $domain = "", $type = "accounts", $limit = 0, $offset = 0, $apply = 0, $max = 0) {
		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<SearchDirectoryRequest xmlns="urn:zimbraAdmin"';
		if ($limit != 0)
			$soapmessage .= ' limit="' . $limit . '"';
		if ($offset != 0)
			$soapmessage .= ' offset="' . $offset . '"';
		if ($domain != "")
			$soapmessage .= ' domain="' . $domain . '"';
		if ($max != 0)
			$soapmessage .= ' maxResults="' . $max . '"';
		if ($type != "")
			$soapmessage .= ' types="' . $type . '"';
		$soapmessage .= '>
		<query>' . $query . '</query>
		</SearchDirectoryRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		#print_r($res);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHDIRECTORYRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['SEARCHDIRECTORYRESPONSE'];
		return false;
	}

	function zimbra_get_service_status() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetServiceStatusRequest xmlns="urn:zimbraAdmin">
		</GetServiceStatusRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVICESTATUSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETSERVICESTATUSRESPONSE'];
		return false;
	}

	function zimbra_maintain_tables() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<MaintainTablesRequest xmlns="urn:zimbraAdmin">
		</MaintainTablesRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['MAINTAINTABLESRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['MAINTAINTABLESRESPONSE'];
		return false;
	}

	function zimbra_get_all_volumes() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetAllVolumesRequest xmlns="urn:zimbraAdmin">
		</GetAllVolumesRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLVOLUMESRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETALLVOLUMESRESPONSE'];
		return false;
	}

	function zimbra_get_current_volumes() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetCurrentVolumesRequest xmlns="urn:zimbraAdmin">
		</GetCurrentVolumesRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETCURRENTVOLUMESRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETCURRENTVOLUMESRESPONSE'];
		return false;
	}

	function zimbra_get_version_info() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetVersionInfoRequest xmlns="urn:zimbraAdmin">
		</GetVersionInfoRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETVERSIONINFORESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETVERSIONINFORESPONSE'];
		return false;
	}

	function zimbra_get_license_info() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetlicenseInfoRequest xmlns="urn:zimbraAdmin">
		</GetLicenseInfoRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETLICENSEINFORESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETLICENSEINFORESPONSE'];
		return false;
	}

	function zimbra_get_zimlet_status() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<GetZimletStatusRequest xmlns="urn:zimbraAdmin">
		</GetZimletStatusRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['GETZIMLETSTATUSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['GETZIMLETSTATUSRESPONSE'];
		return false;
	}

	function zimbra_dump_sessions() {

		$xml = new xml2Array();
		$this->set_zimbra_header();
		$soapmessage = $this->soapheader . '
		<DumpSessionsRequest xmlns="urn:zimbraAdmin">
		</DumpSessionsRequest>
		</soap:Body>
		</soap:Envelope>';
		curl_setopt($this->curlhandle, CURLOPT_POSTFIELDS, $soapmessage);
		if (!($zimbraSOAPResponse = curl_exec($this->curlhandle))) {
			$this->zimbraerrno = curl_errno($this->curlhandle);
			$this->zimbraerror = curl_error($this->curlhandle);
			return false;
		}

		$res = $xml->parse($zimbraSOAPResponse);
		if (isset ($res['SOAP:ENVELOPE']['SOAP:BODY']['DUMPSESSIONSRESPONSE']))
			return $res['SOAP:ENVELOPE']['SOAP:BODY']['DUMPSESSIONSRESPONSE'];
		return false;
	}

	function zimbra_is_domain_locked($domain) {
		$out = $this->zimbra_get_domain($domain);
		foreach ($out['DOMAIN']['A'] as $a) {
			if ($a['N'] == "zimbraDomainStatus")
				if ($a['DATA'] == "locked")
					return true;
				else
					return false;
		}
		return false;
	}

	function zimbra_is_account_locked($account) {
		$out = $this->zimbra_get_account($account);
		foreach ($out['ACCOUNT']['A'] as $a) {
			if ($a['N'] == "zimbraAccountStatus")
				if ($a['DATA'] == "locked")
					return true;
				else
					return false;
		}
		return false;
	}

	function zimbra_lock_domain($domain) {
		return $this->zimbra_modify_domain($domain, array (
			'zimbraDomainStatus' => 'locked'
		));
	}

	function zimbra_unlock_domain($domain) {
		return $this->zimbra_modify_domain($domain, array (
			'zimbraDomainStatus' => 'active'
		));
	}

	function zimbra_lock_account($account) {
		return $this->zimbra_modify_account($account, array (
			'zimbraAccountStatus' => 'locked'
		));
	}

	function zimbra_unlock_account($account) {
		return $this->zimbra_modify_account($account, array (
			'zimbraAccountStatus' => 'active'
		));
	}

	function zimbra_get_id_from_domain($domain) {
		$out = $this->zimbra_get_domain($domain);
		if ($out == false)
			return false;
		return $out['DOMAIN']['ID'];
	}

	function zimbra_get_id_from_server($server) {
		$out = $this->zimbra_get_domain($server);
		if ($out == false)
			return false;
		return $out['SERVER']['ID'];
	}

	function zimbra_get_id_from_account($name) {
		$out = $this->zimbra_get_account($name);
		if ($out == false)
			return false;
		return $out['ACCOUNT']['ID'];
	}

	function zimbra_get_id_from_cos($name) {
		$out = $this->zimbra_get_cos($name);
		if ($out == false)
			return false;
		return $out['COS']['ID'];
	}

	function zimbra_get_aliases($email) {
		$out = array ();
		$ret = $this->zimbra_get_account($email);
		foreach ($ret['ACCOUNT']['A'] as $a) {
			if ($a['N'] == "zimbraMailAlias"){
				$out[] = $a['DATA'];				
			}
		}
		return $out;
	}

}
?>