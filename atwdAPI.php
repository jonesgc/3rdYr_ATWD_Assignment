<?php

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];
$base = 'GBP';

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
    
    switch ($method)
    {
        case 'GET': respondGET($query, $base, $xml);break;
        case 'PUT':break;
        case 'POST':break;
        case 'DELETE':break;
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
        $name = (string)$currency->name;
        $rate = (string)$currency->rate;
        
        if($name == $base)
        {
            //Cannot alter the base currency in this function.
        }
        elseif($name == $origin)
        {
            $originVal = $rate;
        }
        elseif($name == $target)
        {
            $targetVal = $rate;
        }
    }

    //Perform the conversion, using the base currency as a "stepping stone"
    $newAmount = ($amount * $originVal)/$targetVal;

    //Find the get response XML template.
    if (file_exists('curData.xml')) 
    {
        $result = simplexml_load_file('getRes.xml');

        $dt = date('d/m/y\ m:h');
        $result->conv->at = $dt;

        //Origin or from return values input into response xml.
        $result->conv->from->code = $origin;
        $result->conv->from->rate = $originVal;
        //Missing location data!
        $result->conv->from->amnt = $amount;
        
        //Target or to values input into response xml.
        $result->conv->to->code = $target;
        $result->conv->to->amnt = $newAmount;
        return $result;
    }
    else
    {
        //Error
        echo "Cant find get response template";
        return "Error";
    }
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
    echo $result->asXML();
}

//echo convertCur($base, $origin, $target, $amount,$xml);
methodController($method, $query, $base, $xml);

?>