<?php

$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

if (($stream = fopen('php://input', "r")) !== FALSE)
    var_dump(stream_get_contents($stream));

if (file_exists('config.xml'))
{
    $config = simplexml_load_file('config.xml');
}
else
{
    echo "Cant find currency data file.";
    //Need to throw a service error here.
}
$base = $config->base;
//Get the exchange rates xml file for use in conversions.
if (file_exists('curData.xml'))
{
    $xml = simplexml_load_file('curData.xml');
}
else
{
    echo "Cant find currency data file.";
}


//Swtitch on the method in the request then call the corisponding function.
function methodController($method, $query, $base, $xml)
{
    //This IF statement differenciates between a delete request and a true POST request. This is a work around, need to find a proper solution.
    if($method == 'POST')
    {
        $headers = getallheaders();
        //print_r($headers);
        if($headers['action'] == 'DELETE')
        {
            $method = 'DELETE';
        }
    }
    switch ($method)
    {
        case 'GET': respondGET($query, $base, $xml);break;
        case 'PUT': respondPUT($xml);break;
        case 'POST': respondPOST($xml);break;
        case 'DELETE': respondDELETE($xml);break;
    }
}


//This function converts between the origin currency and the target currency using the base currency as an intermedary.
//Current solution O-->B-->T
function convertCur($base, $origin, $target, $amount, $xml)
{

    $originVal = 0;
    $targetVal = 0;

    //Get the rates for origin and target vs the base currency.
    foreach ($xml->rates->cur as $currency)
    {
        $name = (string)$currency->name;
        $rate = (string)$currency->rate;

        if($name == $base)
        {
            //Cannot alter the base currency in this function.
        }
        elseif($name == $origin)
        {
            $originVal = $rate;
        }
        elseif($name == $target)
        {
            $targetVal = $rate;
        }
    }

    //Perform the conversion, using the base currency as a "stepping stone"
    $newAmount = ($amount * $originVal)/$targetVal;

    $result = array($origin, $originVal, $amount, $target, $targetVal, $newAmount);

    return $result;
}

//Respond to a GET request.
//Expected response type is XML.
function respondGET ($query, $base, $xml)
{
    $params = (explode('&', $query));
    $origin = $params[0];
    $target = $params[1];
    $amount = $params[2];
    $type = $params[3];
    $result = convertCur($base, $origin, $target, $amount, $xml);

    if($type == 'XML')
    {
        //Find the get response XML template.
        if (file_exists('curData.xml'))
        {
            $res = simplexml_load_file('getResXML.xml');
            
            $res->conv->at = date("d/m/y \ h:i", (int)$xml->updated->dataUpdated);

            //Origin or from return values input into response xml.
            $res->from->code = $origin;
            $res->from->rate = $result[1];
            //Missing location data!
            $res->from->amnt = $amount;

            //Target or to values input into response xml.
            $res->to->code = $target;
            $res->to->amnt = $result[5];

            echo $res->asxml();
        }
        else
        {
            //Error
            echo "Cant find get response template";
        }
    }
    elseif($type == 'JSON')
    {
        $res = json_decode(file_get_contents('getResJSON.json'), true);

        $res['conv']['at'] = date("d/m/y \ h:i", (int)$xml->updated->dataUpdated);
        $res['conv']['rate'] = $result[1];

        //Input origin or from response values into JSON.
        $res['conv']['from']['code'] = $origin;
        //Need currency name.
        //Need location data.
        $res['conv']['from']['amnt' ]= $amount;

        //Input target or to response values into JSON.
        $res['conv']['to']['code'] = $target;
        $res['conv']['to']['amnt'] = $result[5];

        $res = json_encode($res);
        echo $res;
    }

}

function respondPUT($xml)
{
  	//Extract the put data from the php stdin stream.
	$putdata = json_decode(file_get_contents('php://input', true), true);
	$code = $putdata['code'];
	$rate = $putdata['rate'];
    //Need to do the missing data values: country (comma separated), full name of currency

	//Put the new value in the XML file.
	$rates = $xml->rates;
	$cur = $rates->addChild('cur');
    $name = $cur->addChild('name', $code);
    $rate = $cur->addChild('rate', $rate);

    //Code inspired by solution on URL:https://stackoverflow.com/questions/798967/php-simplexml-how-to-save-the-file-in-a-formatted-way/1793240
    //The following lines are not needed for machine readable XML, but are needed to preserve indentation structure.
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    
    $dom->save('curData.xml');

    echo "Putting stuff in the file.";
    
}

function respondPOST($xml)
{   
    //This is because of the type of data coming from the client, need to handel this.
    $postdata = json_decode(file_get_contents('php://input', true), true);
    print_r($postdata);

    //Iterate through the xml file, this is done so multiple values could be changed if need be.
    foreach ($xml->rates->cur as $currency)
    {
        $name = (string)$currency->name;
        $rate = (string)$currency->rate;
        
        if($name == $postdata["code"])
        {
            echo "Found Code match";
            echo $rate;
            $currency->rate = $postdata["rate"];
            echo $rate;
        }
    }

    file_put_contents('curData.xml', $xml->asxml());
    
}

function respondDELETE($xml)
{
    $code = file_get_contents('php://input', true);

    //Iterate through XML looking for code, then set the inative flag to TRUE.
    foreach ($xml->rates->cur as $currency)
    {
        $name = (string)$currency->name;
        $rate = (string)$currency->rate;
        
        if($name == $code)
        {
            echo "Found Code match";
            $currency->inactive = 'TRUE';
        }
    }

    file_put_contents('curData.xml', $xml->asxml());
}

//echo convertCur($base, $origin, $target, $amount,$xml);
methodController($method, $query, $base, $xml);

?>
