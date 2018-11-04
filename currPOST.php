<?php

include_once "config.php";
include_once "generateError.php";


$method = $_SERVER['REQUEST_METHOD'];
$query = $_SERVER['QUERY_STRING'];

function respondPOST($xml)
{
    //This is because of the type of data coming from the client, need to handel this.
    $postdata = json_decode(file_get_contents('php://input', true), true);
    $type = $postdata['type'];

    //Flag variable is used to test if there has been a successful code match.
    $flag = 0;
    //Find code match and update the currency rate.
    foreach ($xml->rates->cur as $currency)
    {
        $code = (string)$currency->name;
        $rate = (string)$currency->rate;

        if($code == $postdata['code'])
        {
            //Old rate is required for response to client.
            define('oldrate', $currency->rate);
            $currency->rate = $postdata['rate'];
            $inactive = $currency->inactive;
            $flag = 1;
        }
    }
    
    if($flag == 0)
    {
        //Check if code input was correct format.
        $test = preg_match('/([A-Z])([^a-z])/', $postdata['code']);
        if(!$test)
        {
            generateError(2200);
        }
        else
        {
            generateError(2400);
        }

    }
    elseif ((!preg_match('/([0-9]+)\.{1}([0-9]+)/', $postdata['rate'])) || ($postdata['rate'] = ""))
    {
        //Check if input rate is a decimal.
        generateError(2100);
    }
    //Check if currency is active.
    elseif($inactive == "TRUE")
    {
        generateError(2500);
    }
    else
    {
        //Only save changes if everything has been validated.
        file_put_contents('curData.xml', $xml->asxml());

        //Send response to client.
        $method = $_SERVER['REQUEST_METHOD'];
        $node = findData($postdata['code'], $xml);
        $locs = $node['loc'];
        $code = $node['code'];
        $name = $node['name'];
        $rate = $node['rate'];

		//Create time and date for when this function was executed.
		$at = date("d M y  h:i");


        if($type == 'XML')
        {
            if (file_exists('templates/postResXML.xml'))
			{
                $res = simplexml_load_file('templates/postResXML.xml');
                $res->at = $at;
                $res->old_rate = constant('oldrate');
                $res->rate = $rate;
				$res->curr->code = $code;
				$res->curr->name = $name;
				$res->curr->loc = $locs;
				
				header('Content-Type: text/xml');
				echo $res->asxml();
			}
			else
			{
				generateError(2500);
			}
        }
        elseif($type == 'JSON')
        {   
            if (file_exists('templates/postResJSON.json'))
			{
				$res = json_decode(file_get_contents('templates/postResJSON.json'), true);

                $res['post']['at'] = date("d M y \ h:i");
                $res['post']['old_rate'] = constant('oldrate');
                $res['post']['rate'] = (string)$rate;
				$res['post']['curr']['code'] = (string)$code;
				$res['post']['curr']['currName'] = (string)$name;
				$res['post']['curr']['loc'] = (string)$locs;

                header('Content-Type: text/json');
				$res = json_encode($res);

				echo $res;
			}
			else
			{
				generateError(2500);
			}
        }
        else
        {
            generateError(2500);
        }
    }

}

?>
