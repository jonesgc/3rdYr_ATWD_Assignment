<?php
//Author: Gregory Jones
//This script will be run once every few hours, url data and app id are contained within config.php
//If there is a difference between the data then the script will update curData with the newer data.

include_once "config.php";

date_default_timezone_set("UTC");


if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
    //Need to throw a service error here.
}


function updateCurDataAcessed ($xml)
{
    //Update time + date accessed.
    $time = $xml->updated->time;
    $date = $xml->updated->date;

    //Get current server date + time.
    $servDate = date("d/m/y");
    $servTime = date("h:i");

    if($date != $servDate)
    {
        $xml->updated->time = $servTime;
        $xml->updated->date = $servDate;
        //echo $time->asxml();
        file_put_contents('curData.xml', $xml->asxml());
    }
}

function updateCurData ($xml, $base, $URL)
{
    //echo $latestURL;
    $latestData = json_decode(file_get_contents($URL));
    
    //print_r($latestData);
    $timestamp = $latestData->{'timestamp'};
    $apiUpdated = date("d/m/y \ h:i", $timestamp);
    //echo $apiUpdated;
    $xml->updated->dataUpdated = $timestamp;
    
    foreach ($xml->rates->cur as $currency)
    {
        //echo $currency->name;
        $code = (string)$currency->code;
        $rate = (string)$currency->rate;
        //echo $currency->rate;
        //echo $latestData->rates->$name;

        if($currency->code == $base)
        {
            //Always set the base currency to 1.
            $currency->rate = 1;
        }
        elseif($currency->code == 'TEST')
        {
            //test is used for debugging only, remove for submission.
        }
        elseif($code == $currency->code)
        {
            $currency->rate = $latestData->rates->$code;
        }

    }

    file_put_contents('curData.xml', $xml->asxml());
}



//header('Content-Type: text/xml');
//echo $xml;

?>
