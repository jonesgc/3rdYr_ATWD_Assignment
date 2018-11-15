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
		//SOme currencies use more than one currency.
		foreach ($obj["currencies"] as $currency)
		{
			if($currency["code"] == $code)
			{
				//Add the full name of the currency, does not matter if this overwrites since it will still be the same.
				$validCurr["currName"] = $currency["name"];

				//Append the location that the currency is used in.
				$validCurr["locs"] = $validCurr["locs"] . $obj["name"] . ",";

				//Remove trailing comma.
				$validCurr["locs"] = rtrim($validCurr["locs"], ',');
			}
		}
	}

	//If either the locations or the name cannot be found it must not be a proper currency.
	if((empty($validCurr['currName'])) || (empty($validCurr['locs'])))
	{
		return FALSE;
	}
	else
	{
		return $validCurr;
	}
}
//Test for above function.
/*
$test = simplexml_load_file('curData.xml');
$node = validCurrCheck("SGD", $test);
if($node == FALSE)
{
	echo "FALSE";
}
else
{
	print_r($node);
}
*/


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

	//Check if the put rate is 0 
	if((float)$rate <= 0.0)
	{
		generateError(2500);
	}
	//If $isValid is empty that means that the code entered was not a valid code according to the ISO standard.
	elseif($isValid == FALSE)
	{
		generateError(2400);
	}
	//Put the new value in the XML file.
	else
	{
		if($code == $node['code'])
		{
			if($node['inactive'] == "FALSE")
			{
				//If the currency is in the file and still active put should throw an error.
				generateError(2500);
			}
			else
			{
				//If the currency is already in the file then the PUT function should re activete it.
				$object = $xml->xpath('//currencies/rates/cur[./code="'.$code.'"]');
				$object[0]->inactive = "FALSE";
				file_put_contents('curData.xml', $xml->asxml());
			}
			
		}
		else
		{
			//Currency is vaid but not in the file.
			$rates = $xml->rates;
			$cur = $rates->addChild('cur');
	    	$curCode = $cur->addChild('code', $code);
			$curName = $cur->addChild('fname', $name);
	    	$curRate = $cur->addChild('rate', $rate);
			$curLocs = $cur->addChild('loc', $locs);
			$inactive = $cur->addChild('inactive', "FALSE");

	    	//Code inspired by solution on URL:https://stackoverflow.com/questions/798967/php-simplexml-how-to-save-the-file-in-a-formatted-way/1793240
	    	//The following lines are not needed for machine readable XML, but are needed to preserve indentation structure for human readability.
	    	$dom = new DOMDocument('1.0');
	    	$dom->preserveWhiteSpace = false;
	    	$dom->formatOutput = true;
	    	$dom->loadXML($xml->asXML());

	    	$dom->save('curData.xml');
		}
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
				$res['put']['curr']['rate'] = (float)$rate;

				header('Content-Type: text/json');
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
