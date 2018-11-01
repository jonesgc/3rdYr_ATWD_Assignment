<?php
include_once "config.php";
include_once "generateError.php";

//This function quires this api:URL HERE	and checks to see if the code input
//is valid in the ISO standard.
function validCurrCheck($code, $xml)
{
$data = json_decode(file_get_contents("https://restcountries.eu/rest/v2/all"),true);
$validCurr = array("currName"=>"", "locs"=>"");
	foreach ($data as $obj)
	{
		if($obj["currencies"][0]["code"] == $code)
		{
			$validCurr = $validCurr . $obj["currencies"];
		}
    	foreach ($xml->rates->cur as $currency)
    	{
        	if($code == $obj["currencies"][0]["code"])
        	{
				$validCurr["currName"] = $obj["currencies"][0]["name"];
				//Need to do locations.
        	}
    	}

	}
return $validCurr;
}
//Test for above code.
/*if(validCurrCheck($code, $xml) == 1)
{
	echo "Valid!";
}*/



function respondPUT($xml)
{
  	//Extract the put data from the php stdin stream.
	$putdata = json_decode(file_get_contents('php://input', true), true);
	$code = $putdata['code'];
	$name = $putdata['fname'];
	$rate = $putdata['rate'];
	$locs = $putdata['locs'];

	//Check if the currency is valid one according to the ISO standard.
	$isValid = validCurrCheck($code, $xml);

	//Search XML document for a matching code.
	$node = findData($code, $xml);
	if($isValid == 0)
	{
		generateError(2400);
	}
	//Check if the currency is already in the XML file.
	elseif($code == $node['code'])
	{
		echo "Code matches one in use.";
	}
	//Put the new value in the XML file.
	else
	{
		$rates = $xml->rates;
		$cur = $rates->addChild('cur');
	    $code = $cur->addChild('code', $code);
		$name = $cur->addChild('fname', $name);
	    $rate = $cur->addChild('rate', $rate);
		$locs = $cur->addChild('loc', $locs);
		$inactive = $cur->addChild('inactive', "FALSE");

	    //Code inspired by solution on URL:https://stackoverflow.com/questions/798967/php-simplexml-how-to-save-the-file-in-a-formatted-way/1793240
	    //The following lines are not needed for machine readable XML, but are needed to preserve indentation structure.
	    $dom = new DOMDocument('1.0');
	    $dom->preserveWhiteSpace = false;
	    $dom->formatOutput = true;
	    $dom->loadXML($xml->asXML());

	    $dom->save('curData.xml');

		//Create time and date for when this function was executed.
		$at = date("d/m/y h:i");
		//Send Reponse to client.
		if($type == "XML")
		{
			header('Content-Type: text/xml');
		}
		elseif($type == "JSON")
		{

		}
		
	}

}


?>
