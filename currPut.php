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
			$validCurr["locs"] = $validCurr["locs"] . $obj["name"] . ",";
		}

    	foreach ($xml->rates->cur as $currency)
    	{
        	if($code == $obj["currencies"][0]["code"])
        	{
				$validCurr["currName"] = $obj["currencies"][0]["name"];
        	}
    	}

	}
return $validCurr;
}



function respondPUT($xml)
{
  	//Extract the put data from the php stdin stream.
	$putdata = json_decode(file_get_contents('php://input', true), true);
	$code = $putdata['code'];
	$rate = $putdata['rate'];
	$type = $putdata['type'];
	//Check if the currency is valid one according to the ISO standard.
	$isValid = validCurrCheck($code, $xml);

	//The rest of the data needed to complete the node is returned from validCurrCheck, such as full name of the currency and the locations.
	$name = $isValid['currName'];
	$locs = $isValid['locs'];

	//Search XML document for a matching code.
	$node = findData($code, $xml);

	//If $isValid is empty that means that the code entered was not a valid code according to the ISO standard.
	if($isValid == empty($isValid))
	{
		generateError(2400);
	}
	//Check if the currency is already in the XML file.
	elseif($code == $node['code'])
	{
		generateError(2500);
	}
	//Put the new value in the XML file.
	else
	{
		$rates = $xml->rates;
		$cur = $rates->addChild('cur');
	    $curCode = $cur->addChild('code', $code);
		$curName = $cur->addChild('fname', $name);
	    $curRate = $cur->addChild('rate', $rate);
		$curLocs = $cur->addChild('loc', $locs);
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
			
			if (file_exists('templates/putResXML.xml'))
			{
				
				$res = simplexml_load_file('templates/putResXML.xml');
				$res->at = date("d M y  h:i");
				$res->curr->code = $code;
				$res->curr->name = $name;
				$res->curr->loc = $locs;
				$res->curr->rate = $rate;

				header('Content-Type: text/xml');
				echo $res->asxml();
			}
			else
			{
				generateError(2500);
			}
		}
		elseif($type == "JSON")
		{
			if (file_exists('templates/putResJSON.json'))
			{
				$res = json_decode(file_get_contents('templates/putResJSON.json'), true);

				$res['put']['at'] = date("d M y \ h:i");
				$res['put']['curr']['code'] = (string)$code;
				$res['put']['curr']['currName'] = (string)$name;
				$res['put']['curr']['loc'] = (string)$locs;
				$res['put']['curr']['rate'] = (string)$rate;

				$res = json_encode($res);

				echo $res;
			}
			else
			{
				generateError(2500);
			}
		}
		
	}

}


?>
