<?php
//Author: Gregory Jones
//This script will be run once every few hours, data is pulled from http://www.openrates.io/. 
//If there is a difference between the data then the script will update curData with the newer data. 

//This is the base currency of the api. 
$base = 'GBP';

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

function updateCurData ($xml, $base)
{
    //Checks if there are differences between the data in the xml file and the data in the currency feed.
    $latestURL = "http://api.openrates.io/latest" . '?base=' . $base;
    $latestData = json_decode(file_get_contents($latestURL));
    echo $latestURL;
    print_r($latestData);
    
    foreach ($xml->rates->cur as $currency) 
    {
        //echo $currency->name;
        $name = (string)$currency->name;
        $rate = (string)$currency->rate;
        //echo $currency->rate;
        //echo $latestData->rates->$name;
        
        if($currency->name == $base)
        {
            //Always set the base currency to 1.
            $currency->rate = 1;
        }
        elseif($name == $currency->name)
        {
            $currency->rate = $latestData->rates->$name;
        }
        
        
    }

    file_put_contents('curData.xml', $xml->asxml());
}



//header('Content-Type: text/xml');
//echo $xml;
updateCurData($xml, $base);
updateCurDataAcessed($xml);
?>