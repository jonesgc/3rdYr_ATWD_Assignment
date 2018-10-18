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



foreach ($data as $obj)
{

    foreach ($xml->rates->cur as $currency)
    {
        //echo $currency->name->asxml();
        //echo " ";
        if($currency->name == $obj["currencies"][0]["code"])
        {  
            $currency->code = $obj["currencies"][0]["code"];
            $currency->name = $obj["currencies"][0]["name"];
        }
    }
   
}

    
/*make the new code node
$cur = $currency->name;
$cur->addChild("code", $currency->name);
//Add the location node.
$xml->rates->cur->addChild("loc");

//Change the name to full name.
$xml->rates->cur->name = $obj["currencies"][0]["name"];
*/

//echo $xml->asxml();
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());

$dom->save('curData.xml')
?>