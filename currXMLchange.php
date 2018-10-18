<?php

if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
    //Need to throw a service error here.
}
if (file_exists('config.xml'))
{
    $config = simplexml_load_file('config.xml');
}
else
{
    echo "Cant find currency data file.";
    //Need to throw a service error here.
}

$data = json_decode(file_get_contents("https://restcountries.eu/rest/v2/all"),true);
$test = array_search('USD', $data);
echo $test;


foreach ($xml->rates->cur as $currency)
    {
        foreach ($data as $obj)
        {
            //print_r($obj["currencies"][0]);
            if($currency->name == $obj["currencies"][0]["code"])
            {
                //make the new code node
                $cur = $xml->rates->cur;
                $cur->addChild("code", $currency->name);
                //Add the location node.
                $xml->rates->cur->addChild("loc");
    
                //Change the name to full name.
                $xml->rates->cur->name = $obj["currencies"][0]["name"];
    
                $xml->rates->cur->addChild("inactive", "FALSE");
            }
        }
       
    }
echo $xml->asxml();
file_put_contents('curData.xml', $xml->asxml());
?>