<?php
class MyFault extends SoapFault {
	function __construct() {
		parent::__construct("MyFault","My fault string");
	}
}


class ZmSoapFault extends SOAPFault
{
	/*
	public function __construct($message)
	{
		if ($message->queryError)
		{
			myOwnHandle();
		}
		parent::__construct($message);
	}
	*/


	public function __construct($code, $string)
	{
		parent::__construct($code, $string);
		error_log($this);
	}




	function show()
	{
		echo "exception caught!...<br><br>EXCEPTION START <<<<<<<<<<< <p>";
		echo $this . "<br><br>";
		echo ">>>>>>>>>>>> EXCEPTION END<p>";
	}
}

?>
