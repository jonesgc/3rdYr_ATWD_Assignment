<?php

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

//Get the exchange rates xml file for use in conversions.
if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
}


function methodController($method, $query)
{
    //Swtitch on the method in the request then call the corisponding function.
    switch ($method)
    {
        case 'GET':echo $query;break;
        case 'PUT':break;
        case 'POST':break;
        case 'DELETE':break;
    }
}


function convertCur($base, $origin, $target, $amount, $xml)
{
    //This function converts between the origin currency and the target currency using the base currency as an intermedary.
    //Current solution O-->B-->T
    
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

    $result = ($amount * $originVal)/$targetVal;

    return $result;
}
$base = 'GBP';
$origin = 'DKK';
$amount = 1;
$target = 'JPY';
//echo convertCur($base, $origin, $target, $amount,$xml);
methodController($method, $query);
?>