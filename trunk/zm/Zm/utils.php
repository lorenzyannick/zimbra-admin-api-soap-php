<?php

define(ATTR_SINGLEVALUE, 1);
define(ATTR_MULTIVALUE, 2);

/////////
// XML //
/////////

class xml2Array
{
	var $stack=array();
	var $stack_ref;
	var $arrOutput = array();
	var $resParser;
	var $strXmlData;

	function push_pos(&$pos)
	{
		$this->stack[count($this->stack)]=&$pos;
		$this->stack_ref=&$pos;
	}

	function pop_pos()
	{
		unset($this->stack[count($this->stack)-1]);
		$this->stack_ref=&$this->stack[count($this->stack)-1];
	}

	function parse($strInputXML)
	{
		$this->resParser = xml_parser_create ();
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

		xml_set_character_data_handler($this->resParser, "tagData");

		$this->push_pos($this->arrOutput);

		$this->strXmlData = xml_parse($this->resParser,$strInputXML );
		if(!$this->strXmlData)
		{
			die(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($this->resParser)),
			xml_get_current_line_number($this->resParser)));
		}

		xml_parser_free($this->resParser);

		return $this->arrOutput;
	}

	function tagOpen($parser, $name, $attrs)
	{
		if (isset($this->stack_ref[$name]))
		{
			if (!isset($this->stack_ref[$name][0]))
			{
				$tmp=$this->stack_ref[$name];
				unset($this->stack_ref[$name]);
				$this->stack_ref[$name][0]=$tmp;
			}
			$cnt=count($this->stack_ref[$name]);
			$this->stack_ref[$name][$cnt]=array();
			if (isset($attrs))
				$this->stack_ref[$name][$cnt]=$attrs;
			$this->push_pos($this->stack_ref[$name][$cnt]);
		}
		else
		{
			$this->stack_ref[$name]=array();
			if (isset($attrs))
				$this->stack_ref[$name]=$attrs;
			$this->push_pos($this->stack_ref[$name]);
		}
	}

	function tagData($parser, $tagData)
	{
		if(trim($tagData))
		{
			if(isset($this->stack_ref['DATA']))
				$this->stack_ref['DATA'] .= $tagData;
			else
				$this->stack_ref['DATA'] = $tagData;
		}
	}

	function tagClosed($parser, $name)
	{
		$this->pop_pos();
	}
}

function getSoapAttribute($allAttrs, $attrName, $multisingle=ATTR_SINGLEVALUE)
{
		$attrs = array ();
		foreach ($allAttrs as $a) {
			if ($a['N'] == $attrName){
				$attrs[] = $a['DATA'];
				if ($multisingle == ATTR_SINGLEVALUE) break;
			}
		}

		if ($multisingle == ATTR_MULTIVALUE)
			return $attrs;
		else
			return $attrs[0];
}

////////////////
// Exceptions //
////////////////

function print_exception($ex)
{
	if (PHP_SAPI != "cli") {
		$nl = "<br/>";
		$pre1 = "<pre>";
		$pre2 = "</pre>";
	} else {
		$nl = "\n";
		$pre1 = $nl.$nl;
		$pre2 = $nl.$nl;
	}
	echo "Exception caught!...".$nl.$nl."EXCEPTION START <<<<<<<<<<< ";
	echo $pre1 . $ex . $pre2;
	echo ">>>>>>>>>>>> EXCEPTION END".$nl;
}

///////////////
// Variables //
///////////////

function print_var($var, $titre = "")

{
	if (PHP_SAPI != "cli") {
		$nl = "<br/>";
		$pre1 = "<pre>";
		$pre2 = "</pre>";
		if ($titre) {
			$sep = "<hr>";
			$title = "<h1>".$titre."</h1>";
		}
	} else {
		$nl = "\n";
		$pre1 = $nl.$nl;
		$pre2 = $nl.$nl;
		if ($titre) {
			$sep = str_repeat("-", 80).$nl;
			$title = "\033[1m"."--- ".$titre." ---"."\033[0m";
		}
	}

	echo $title;
	echo $pre1;
	print_r($var);
	echo $pre2;
	echo $sep;
}

function parse_args($argv){
	array_shift($argv);
	$out = array();
	foreach ($argv as $arg){
		if (substr($arg,0,2) == '--'){
			$eqPos = strpos($arg,'=');
			if ($eqPos === false){
				$key = substr($arg,2);
				$out[$key] = isset($out[$key]) ? $out[$key] : true;
			} else {
				$key = substr($arg,2,$eqPos-2);
				$out[$key] = substr($arg,$eqPos+1);
			}
		} else if (substr($arg,0,1) == '-'){
			if (substr($arg,2,1) == '='){
				$key = substr($arg,1,1);
				$out[$key] = substr($arg,3);
			} else {
				$chars = str_split(substr($arg,1));
				foreach ($chars as $char){
					$key = $char;
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				}
			}
		} else {
			$out[] = $arg;
		}
	}
	return $out;
}

/////////////
// Account //
/////////////

function isAccountId($str)
{
	$syntaxe = '#[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}

function isAccountName($str)
{
	$syntaxe = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}

function getAccountType($str)
{
	$outputType = null;
	if(isAccountName($str))
		$outputType = "name";
	else if (isAccountId($str))
		$outputType = "id";
	else
		echo "Unknown AccountType";

	return $outputType;
}

////////////
// Domain //
////////////

function isDomainId($str)
{
	$syntaxe = '#[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}


function isDomainName($str)
{
	$syntaxe = '#([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}



function getDomainType($str)
{
	$outputType = null;
	if(isDomainName($str))
		$outputType = "name";
	else if (isDomainId($str))
		$outputType = "id";
	else
		echo "Unknown DomainType";

	return $outputType;
}

////////////
// Server //
////////////

function isServerId($str)
{
	$syntaxe = '#[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}

function isServerName($str)
{
	$syntaxe = '#([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}#';
	if(preg_match($syntaxe,$str))
		return true;
	else
		return false;
}

function getServerType($str)
{
	$outputType = null;
	if(isServerName($str))
		$outputType = "name";
	else if (isServerId($str))
		$outputType = "id";
	else
		echo "Unknown ServerType";

	return $outputType;
}

?>
