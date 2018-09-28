<?php
//Author: Gregory Jones
//This script will be run once every few hours, it will check the curData.xml file against the infomation pulled from openexchangerates.
//If there is a difference between the data then the script will update curData with the newer data. 

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
        echo $time;
        file_put_contents('curData.xml', $xml->asxml());
    }
}

//header('Content-Type: text/xml');
//echo $xml;

updateCurDataAcessed($xml);
?>