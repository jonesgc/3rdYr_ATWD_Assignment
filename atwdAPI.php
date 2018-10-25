<?php

include_once "config.php";
include_once "generateError.php";
include_once "currGet.php";
include_once "currPost.php";

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

echo "BITCONNECT";


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
//Base currency is declared in the config.php.
function methodController($method, $query, $base, $xml)
{
    //This IF statement differenciates between a delete request and a true POST request. This is a work around, need to find a proper solution.
    if($method == 'POST')
    {
        $headers = getallheaders();
        //print_r($headers);
        
    } 
    switch ($method)
    {
        case 'GET': respondGET($query, $base, $xml);break;
        case 'PUT': respondPUT($xml);break;
        case 'POST': respondPOST($xml);break;
        case 'DELETE': respondDELETE($xml);break;
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
