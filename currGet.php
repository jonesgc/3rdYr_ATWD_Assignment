<?php

include_once "config.php";
include_once "generateError.php";



$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];


//Get the XML file containing currency data.
if (file_exists('config.xml'))
{
    $config = simplexml_load_file('config.xml');
}
else
{
    generateError(1500,"XML");
}

//----FUNCTIONS----

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



//Respond to a GET request, response is echoed to client in format requested.
//Base is declared in config.php.
function respondGET ($query, $base, $xml)
{

    //Extract variables from query string, implemented for readability.
    $params = (explode('&', $query));

    //Check if query string is empty.


    $origin = $params[0];
    $target = $params[1];
    $amount = $params[2];
    $type = $params[3];

    //Validate parameters, checking if codes do not contain numbers and are all caps, and numbers are decimals.
    $oTest = preg_match('/([A-Z])([^a-z])/', $origin);
    $tTest = preg_match('/([A-Z])([^a-z])/', $target);

    //These tests check if the input parameter is the correct style and exists.
    if(!$oTest)
    {
        if($origin == "")
        {
            generateError(1000,"XML");
        }
        else
        {
            generateError(1100, "XML");
        }
    }
    elseif(!$tTest)
    {
        if($target == "")
        {
            generateError(1000,"XML");
        }
        else
        {
            generateError(1100, "XML");
        }
    }
    /*//Check if either of the other parameters are missing.
    elseif(($amount = "") || ($type = ""))
    {
        generateError(1000, "XML");
    }*/
    //Check response type, must match either XML or JSON.
    elseif(($type != "JSON") && ($type != "XML"))
    {
        generateError(1400,"XML");
    }
    //Continue function execution.
    else
    {
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
            $err = $result[1];
            generateError($err, $type);
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


}
?>
