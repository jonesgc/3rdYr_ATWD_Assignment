<?php
//Author: Gregory Jones
//This script will be run once every few hours, it will check the curData.xml file against the infomation pulled from openexchangerates.
//If there is a difference between the data then the script will update curData with the newer data. 

$id = '08c94e1539cd46c69ef98a7a2a94ca7a';

if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else{
    echo "Cant find currency data file.";
}


function updateCurDataAcessed ($xml)
{
    //Update time + date accessed.
    $time = $xml->updated['time'];
    $date = $xml->updated['date'];

    //Get current server date + time.
    $servDate = date("d/m/y");
    $servTime = date("h:i");

    if($time != $servTime)
    {
        $xml->updated['time'] = $servTime;
        $xml->updated['date'] = $servDate;
        //echo $time->asxml();
        file_put_contents('curData.xml', $xml->asxml());
    }
}

function updateCurData ($xml, $id)
{
    //Checks if there are differences between the data in the xml file and the data in the currency feed.
    //Using USD as the base currency, hence why USD is 1. 
    $latestURL = "https://openexchangerates.org/api/latest.json?app_id=" . $id;
    $latestData = json_decode(file_get_contents($latestURL), true);
    print_r($latestData);
    foreach ($xml->rates->cur->rate as $cur) 
    {
        echo $cur;
    }
}

//echo $xml->rates->USD;
//header('Content-Type: text/xml');
//echo $xml;
updateCurData($xml, $id);
updateCurDataAcessed($xml);
?>