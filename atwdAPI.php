<?php

include_once "config.php";
include_once "generateError.php";
include_once "currGet.php";
include_once "currPost.php";
include_once "curDataMonitor.php";
include_once "currPut.php";
include_once "currDelete.php";

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
    
    //This IF statement differenciates between a delete request and a true POST request.
    if($method == 'POST')
    {
        $headers = getallheaders();
        foreach($headers as $header)
        {
            if($header == 'DELETE')
            {
                $method = 'DELETE';
            }
        }
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

//echo convertCur($base, $origin, $target, $amount,$xml);
methodController($method, $query, $base, $xml, $URL);

?>
