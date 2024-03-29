<?php

include_once (__DIR__."/lib/config.php");
include_once (__DIR__."/lib/generateError.php");
include_once (__DIR__."/lib/currGet.php");
include_once (__DIR__."/lib/currPost.php");
include_once (__DIR__."/lib/curDataMonitor.php");
include_once (__DIR__."/lib/currPut.php");
include_once (__DIR__."/lib/currDelete.php");

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
    //This bloc also checks the request headers for post requests to see if they come in the required
    //format for the $_POST variable in php.
    if($method == 'POST')
    {
        $headers = getallheaders();
        $form = FALSE;
        foreach($headers as $header)
        {
            if($header == 'DELETE')
            {
                $method = 'DELETE';
            }
            elseif($header == 'application/x-www-form-urlencoded')
            {
               $form = TRUE;
            }
        }
    }
    switch ($method)
    {
        case 'GET': respondGET($query, $base, $xml);break;
        case 'PUT': respondPUT($xml);break;
        case 'POST': respondPOST($xml , $form);break;
        case 'DELETE': respondDELETE($xml);break;
    }
}

methodController($method, $query, $base, $xml, $URL);

?>
