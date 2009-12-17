<?php

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

////////////////
// Exceptions //
////////////////
function print_exception($ex)
{
	echo "Exception caught!...<br><br>EXCEPTION START <<<<<<<<<<< <p>";
	echo $ex . "<br><br>";
	echo ">>>>>>>>>>>> EXCEPTION END<p>";
}



///////////////
// Variables //
///////////////
function print_var($var, $titre = "")
{
	if(is_array($var))
	{
		echo "<h1>$titre</h1>";
                echo "<pre>";
		print_r($var);
		echo "</pre>";
                echo "<hr>";
	}
	else // if(is_string($var))
	{
                echo "<h1>$titre</h1>";
		echo "<br />";
		print_r($var);
		echo "<br />";
                echo "<hr>";
	}
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
                $outputType = "mmmmm";
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
            echo "ggggggggggggggggg";
            
	return $outputType;
}










///////////
// Trash //
///////////
/*
function &composeArray($array, $elements, $value=array())
{
global $XML_LIST_ELEMENTS;

// get current element
$element = array_shift($elements);

// does the current element refer to a list
if(sizeof($elements) > 0)
{
    $array[$element][sizeof($array[$element])-1] = &composeArray($array[$element][sizeof($array[$element])-1], $elements, $value);
}
else // if (is_array($value))
{
    $array[$element][sizeof($array[$element])] = $value;
}

return $array;
} // end composeArray 




function makeXMLTree($data) 
{
// create parser
$parser = xml_parser_create();
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
xml_parse_into_struct($parser,$data,$values,$tags);
xml_parser_free($parser);

// we store our path here
$hash_stack = array();

// this is our target
$ret = array();
foreach ($values as $key => $val) {

    switch ($val['type']) {
        case 'open':
            array_push($hash_stack, $val['tag']);
            if (isset($val['attributes']))
                $ret = composeArray($ret, $hash_stack, $val['attributes']);
            else
                $ret = composeArray($ret, $hash_stack);
        break;

        case 'close':
            array_pop($hash_stack);
        break;

        case 'complete':
            array_push($hash_stack, $val['tag']);
            $ret = composeArray($ret, $hash_stack, $val['value']);
            array_pop($hash_stack);

            // handle attributes
            if (isset($val['attributes']))
            {
                foreach($val['attributes'] as $a_k=>$a_v)
                {
                    $hash_stack[] = $val['tag'].'_attribute_'.$a_k;
                    $ret = composeArray($ret, $hash_stack, $a_v);
                    array_pop($hash_stack);
                }
            }

        break;
    }
}

return $ret;
}

*/








?>
