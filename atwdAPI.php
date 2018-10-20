<?php

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];
include_once "config.php";
include_once "generate_error.php";

if (file_exists('config.xml'))
{
    $config = simplexml_load_file('config.xml');
}
else
{
    echo "Cant find currency data file.";
    //Need to throw a service error here.
}
$base = $config->base;
//Get the exchange rates xml file for use in conversions.
if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
}




//Swtitch on the method in the request then call the corisponding function.
function methodController($method, $query, $base, $xml)
{
    //This IF statement differenciates between a delete request and a true POST request. This is a work around, need to find a proper solution.
    if($method == 'POST')
    {
        $headers = getallheaders();
        //print_r($headers);
        if($headers['action'] == 'DELETE')
        {
            $method = 'DELETE';
        }
    } 
    switch ($method)
    {
        case 'GET': respondGET($query, $base, $xml);break;
        case 'PUT': respondPUT($xml);break;
        case 'POST': respondPOST($xml);break;
        case 'DELETE': respondDELETE($xml);break;
    }
}


//This function converts between the origin currency and the target currency using the base currency as an intermedary.
//Current solution O-->B-->T
function convertCur($base, $origin, $target, $amount, $xml)
{

    $originVal = 0;
    $targetVal = 0;
    //Get the rates for origin and target vs the base currency.
    foreach ($xml->rates->cur as $currency)
    {
        $code = (string)$currency->code;
        $rate = (string)$currency->rate;

        if($code == $base)
        {
            //Cannot alter the base currency in this function.
        }
        elseif($code == $origin)
        {
            $originVal = $rate;
        }
        elseif($code == $target)
        {
            $targetVal = $rate;
        }
    }
	//Check if originVal and targetVal are still zero, if so that means the code
	//has not been matched.
	if(($originVal == 0) || ($targetVal == 0))
	{
		//This sends the error infomation to the parent function which is
		//responsible for passing the proper error to the client.
		$err = array("ERROR", "1200", "");
    
		if($originVal == 0)
		{
			$err[2] =  "From code not found";
		}
		elseif($targetVal == 0)
		{
			$err[2] = "To code not found";
        }
        elseif(($originVal == 0) && ($targetVal))
        {
            $err[3] = "Both codes not found";
        }
		return $err;
    }
    //Check if the amount is a decimal.
    elseif (!preg_match('/\./', $amount))
    {   
        $err = array("ERROR", "1300");
        return $err;
    }
	else
	{
		//Perform the conversion, using the base currency as a "stepping stone"
	    $newAmount = ($amount / $originVal) * $targetVal;
	    $result = array($origin, $originVal, $amount, $target, $targetVal, $newAmount);

	    return $result;
	}

}
//This function loops through the XML file finding a match based on the code, it then returns an array with the data for that node.
//The rate described is the rate vs the base currency.
function findData($code, $xml)
{
    $node = array('code'=>"", 'name'=>"", 'loc'=>"", 'rate'=> 0);
    foreach($xml->rates->cur as $currency)
            {
                if($currency->code == $code)
                {
                    $node['code'] = $currency->code;
                    $node['name'] = $currency->name;
                    $node['loc'] = $currency->loc;
                    $node['rate'] = $currency->rate;
                }
            }
    return $node;
}
//Respond to a GET request.
//Expected response type is XML.
function respondGET ($query, $base, $xml)
{
    $params = (explode('&', $query));
    $origin = $params[0];
    $target = $params[1];
    $amount = $params[2];
    $type = $params[3];
    $result = convertCur($base, $origin, $target, $amount, $xml);

     //Get the data for the response from XML file.
     $oDat = findData($origin, $xml);
     $oCurrName = $oDat['name'];
     $oLocs = $oDat['loc'];

     $tDat = findData($target, $xml);
     $tCurrName = $tDat['name'];
     $tLocs = $tDat['loc'];

	//Catch an error in currency codes being wrong.
	if($result[0] == "ERROR")
	{
        //Throw an error depending on what cause the convert cur function to error.
        generate_error($result, $type);
	}
    elseif($type == 'XML')
    {
        //Find the get response XML template.
        if (file_exists('curData.xml'))
        {
            $res = simplexml_load_file('getResXML.xml');

            $res->conv->at = date("d/m/y \ h:i", (int)$xml->updated->dataUpdated);
            

            //Origin or from return values input into response xml.
            $res->from->code = $origin;
            $res->from->curr = $oCurrName;
            $res->from->rate = $result[1];
            $res->from->loc = $oLocs;
            $res->from->amnt = $amount;
            //Target or to values input into response xml.
            $res->to->code = $target;
            $res->to->curr = $tCurrName;
            $res->to->amnt = $result[5];
            $res->to->loc = $tLocs;

			//Send response to server.
            echo $res->asxml();
        }
        else
        {
            //Error
            echo "Cant find get response template";
        }
    }
    elseif($type == 'JSON')
    {
		//Need to insert try catch.
        $res = json_decode(file_get_contents('getResJSON.json'), true);

        $res['conv']['at'] = date("d/m/y \ h:i", (int)$xml->updated->dataUpdated);
        $res['conv']['rate'] = $result[1];

        //Input origin or from response values into JSON.
        $res['conv']['from']['code'] = $origin;
        $res['conv']['from']['curr'] = $oCurrName;
        $res['conv']['from']['loc'] = $oLocs;
        $res['conv']['from']['amnt' ]= $amount;

        //Input target or to response values into JSON.
        $res['conv']['to']['code'] = $target;
        $res['conv']['to']['curr'] = $tCurrName;
        $res['conv']['to']['loc'] = $tLocs;
        $res['conv']['to']['amnt'] = $result[5];

        $res = json_encode($res);

		//Send response to client.
        echo $res;
    }

}

function respondPUT($xml)
{
  	//Extract the put data from the php stdin stream.
	$putdata = json_decode(file_get_contents('php://input', true), true);
	$code = $putdata['code'];
	$rate = $putdata['rate'];
    //Need to do the missing data values: country (comma separated), full name of currency

	//Put the new value in the XML file.
	$rates = $xml->rates;
	$cur = $rates->addChild('cur');
    $code = $cur->addChild('name', $code);
    $rate = $cur->addChild('rate', $rate);

    //Code inspired by solution on URL:https://stackoverflow.com/questions/798967/php-simplexml-how-to-save-the-file-in-a-formatted-way/1793240
    //The following lines are not needed for machine readable XML, but are needed to preserve indentation structure.
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());

    $dom->save('curData.xml');

}

function respondPOST($xml)
{
    //This is because of the type of data coming from the client, need to handel this.
    $postdata = json_decode(file_get_contents('php://input', true), true);
    
    //Find code match and update the currency rate.
    foreach ($xml->rates->cur as $currency)
    {
        $code = (string)$currency->name;
        $rate = (string)$currency->rate;

        if($code == $postdata['code'])
        {   
            //Old rate is required for response to client.
            $oldrate = $currency->rate;
            $currency->rate = $postdata['rate'];
        }
    }

    file_put_contents('curData.xml', $xml->asxml());

    //Send response to client.
    $method = $_SERVER['REQUEST_METHOD'];
    $node = findData($postdata['code'], $xml);
    $locs = $node['loc'];
    $code = $node['code'];
    $name = $node['name'];
    $rate = $node['rate'];
    echo <<<EOT
    <?xml version="1.0" encoding="UTF-8"?>
    <method type = $method>
        <at></at>
        <rate>$rate</rate>
        <old_rate>$oldrate</old_rate>
        <curr>
            <code>$code</code>
            <name>$name</name>
            <loc>$locs</loc>
        </curr>
    </method>
EOT;
}

function respondDELETE($xml)
{
    $code = file_get_contents('php://input', true);

    //Iterate through XML looking for code, then set the inative flag to TRUE.
    foreach ($xml->rates->cur as $currency)
    {
        $code = (string)$currency->name;
        $rate = (string)$currency->rate;

        if($code == $code)
        {
            $currency->inactive = "TRUE";

			if($currency->inactive == "TRUE")
			{
				echo "Currency is already inactive.";
			}
        }
    }

    file_put_contents('curData.xml', $xml->asxml());
}

//echo convertCur($base, $origin, $target, $amount,$xml);
methodController($method, $query, $base, $xml);

?>
