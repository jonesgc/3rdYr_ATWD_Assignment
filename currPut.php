<?php
include_once "config.php";
include_once "generateError.php";

//This function quires this api:URL HERE	and checks to see if the code input
//is valid in the ISO standard.
function validCurrCheck($code)
{

}

function respondPUT($xml)
{
  	//Extract the put data from the php stdin stream.
	$putdata = json_decode(file_get_contents('php://input', true), true);
	$code = $putdata['code'];
	$name = $putdata['fname'];
	$rate = $putdata['rate'];
	$locs = $putdata['locs'];

    //Need to do the missing data values: country (comma separated), full name of currency

	//Put the new value in the XML file.
	$rates = $xml->rates;
	$cur = $rates->addChild('cur');
    $code = $cur->addChild('code', $code);
	$name = $cur->addChild('fname', $name);
    $rate = $cur->addChild('rate', $rate);
	$locs = $cur->addChild('loc', $locs);

    //Code inspired by solution on URL:https://stackoverflow.com/questions/798967/php-simplexml-how-to-save-the-file-in-a-formatted-way/1793240
    //The following lines are not needed for machine readable XML, but are needed to preserve indentation structure.
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());

    $dom->save('curData.xml');


	//Send Reponse to client.
	
}


?>
