<?php

include_once "config.php";
include_once "generateError.php";
//currGet is required since for the findData function.
include_once "currGet.php";

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

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

    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<method type = "'.$method.'">';
    echo    '<at></at>';
    echo    '<rate>'.$rate.'</rate>';
    echo    '<old_rate>'.$oldrate.'</old_rate>';
    echo    '<curr>';
    echo        '<code>'.$code.'</code>';
    echo        '<name>'.$name.'</name>';
    echo        '<loc>'.$locs.'</loc>';
    echo    '</curr>';
    echo '</method>';
}


//Get the XML file containing currency data.
if (file_exists('config.xml'))
{
    $config = simplexml_load_file('config.xml');
}
else
{
    generateError(1500,"XML");
}




?>