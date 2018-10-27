<?php

include_once "config.php";
include_once "generateError.php";
include_once "currGet.php";
include_once "currPost.php";
include_once "curDataMonitor.php";
include_once "currPut.php";

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

date_default_timezone_set("UTC");

//Get the exchange rates xml file for use in conversions.
if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
}
$URL = $apiLatest . $apiID;

//Swtitch on the method in the request then call the corisponding function.
//Base currency is declared in the config.php.
function methodController($method, $query, $base, $xml, $URL)
{
    //Perform a check on if the xml file has not been updated within 12 hours.
    $localTime = time();
    $dataUpdated = $xml->updated->dataUpdated;
    //43200 is the value in secconds for 12 hours.
    if(($localTime - $dataUpdated) > 43200)
    {
        updateCurData($xml, $base, $URL);
        updateCurDataAcessed($xml);
    }
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
methodController($method, $query, $base, $xml, $URL);

?>
