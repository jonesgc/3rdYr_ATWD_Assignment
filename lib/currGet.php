<?php

include_once "config.php";
include_once "generateError.php";

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];


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
            if($origin == $base)
            {
                $originVal = 1;
            }
            if($target == $base)
            {
                $targetVal = 1;
            }

        }
        elseif(($code == $origin) && ($code == $target))
        {
            $originVal = $rate;
            $targetVal = $rate;
        }
        elseif($code == $origin)
        {
            $originVal = $rate;
        }
        elseif($code == $target)
        {
            $targetVal = $rate;
            //echo $rate; 
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
            echo "both";
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
        //Format the number to 4 digits after the decimal.
        $newAmount = number_format($newAmount, 4, '.', '');
	    $result = array($origin, $originVal, $amount, $target, $targetVal, $newAmount);

	    return $result;
	}
}



//Respond to a GET request, response is echoed to client in format requested.
//Base is declared in config.php.
function respondGET ($query, $base, $xml)
{
    //Check if query string is empty.
    if(empty($_GET))
    {
        generateError(1000);
        die;
    }

    $queryFormat = array('from', 'to', 'amnt', 'format');

    //Validate the keys for the get query.
    $keys = array_keys($_GET);

    //Format is optional and should default to XML
    if(array_key_exists('format', $_GET))
    {
        if(count($keys) < 4)
        {
            //Required parameter missing.
            generateError(1000);
            die;
        }
    }
    else
    {
        //Format missing but is optional so only error if one required is missing.
        if(count($keys) < 3)
        {
            generateError(1000);
            die;
        }
    }



    
    if($keys[0] != 'from')
    {
        generateError(1100);
        die;
    }
    elseif($keys[1] != 'to')
    {
        generateError(1100);
        die;
    }
    elseif($keys[2] != 'amnt')
    {
        generateError(1100);
        die;
    }
    

    //Check if values are input in the get query
    //from
    if(array_key_exists('from', $_GET))
    {
        $origin = $_GET['from'];
    }
    else
    {
        generateError(1000);
        die;    
    }

    //to
    if(array_key_exists('to', $_GET))
    {
        $target = $_GET['to'];
    }
    else
    {
        generateError(1000);
        die;    
    }
    
    //amount
    if(array_key_exists('amnt', $_GET))
    {
        $amount = $_GET['amnt'];
    }
    else
    {
        generateError(1000);
        die;    
    }
    
    //format
    if(array_key_exists('format', $_GET))
    {
        $type = $_GET['format'];
        $type = strtoupper($type);
    }
    else
    {
        $type = "XML";
    }

    //Validate parameters, checking if codes do not contain numbers and are all caps.
    $oTest = preg_match('/([A-Z]){3}/', $origin);
    $tTest = preg_match('/([A-Z]){3}/', $target);

    //These tests check if the input parameter is the correct style.
    if(($oTest == FALSE) || ($tTest == FALSE))
    {
        generateError(1100);
    }
    //Check response type, must match either XML or JSON.
    elseif(($type != "JSON") && ($type != "XML"))
    {
        generateError(1400);
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
            //Result 1 contains the error code.
            generateError($result[1]);
        }
        //If either the target or origin currency is listed as inactive generate an error.
        elseif(($oDat['inactive'] == "TRUE") || ($tDat['inactive'] == "TRUE"))
        {
            generateError(1500);
        }
        elseif($type == 'XML')
        {
           //Find the get response XML template.
           if (file_exists('templates/getResXML.xml'))
           {
               $res = simplexml_load_file('templates/getResXML.xml');
               $res->at = date("d M y h:i", (int)$xml->updated->dataUpdated);
               $res->rate = $result[1];

               //Origin or from return values input into response xml.
               $res->from->code = $origin;
               $res->from->curr = $oCurrName;
               $res->from->loc = $oLocs;
               $res->from->amnt = $amount;
               //Target or to values input into response xml.
               $res->to->code = $target;
               $res->to->curr = $tCurrName;
               $res->to->amnt = $result[5];
               $res->to->loc = $tLocs;

               //Send response to server.
               header('Content-Type: text/xml');
               echo $res->asxml();
           }
           else
           {
               //Error, cant find the template.
               generateError(1500);
           }
       }
       elseif($type == 'JSON')
       {
           //Need to insert try catch.
           if(file_exists('templates/getResJSON.json'))
           {
                $res = json_decode(file_get_contents('templates/getResJSON.json'), true);

                $res['conv']['at'] = date("d M y \ h:i", (int)$xml->updated->dataUpdated);
                $res['conv']['rate'] = (float)$result[1];

                //Input origin or from response values into JSON.
                $res['conv']['from']['code'] = (string)$origin;
                $res['conv']['from']['curr'] = (string)$oCurrName;
                $res['conv']['from']['loc'] = (string)$oLocs;
                $res['conv']['from']['amnt' ]= (float)$amount;

                //Input target or to response values into JSON.
                $res['conv']['to']['code'] = (string)$target;
                $res['conv']['to']['curr'] = (string)$tCurrName;
                $res['conv']['to']['loc'] = (string)$tLocs;
                $res['conv']['to']['amnt'] = (float)$result[5];
                
                header('Content-Type: text/json');
                $res = json_encode($res);

                //Send response to client.
                echo $res;
            }
            else
            {
                //Cant find the template.
                generateError(1500);
            }
           
       }
    }

}
//Test for GET from URL
//respondGET($query, $base, "");
?>
