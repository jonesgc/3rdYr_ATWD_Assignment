<?php

include_once 'config.php';
//Purpose of this function is to send an error response to the client, the default format is XML, but an option for JSON.
//The code will generate an error string, which would be the error code it wants to throw.
//Note the errorHash global used in this function is found in config.php

function generateError($code)
{   
    if($GLOBALS['errorType'] == "XML")
    {
        if(file_exists('templates/errorTemplateXML.xml'))
		{
				
			$res = simplexml_load_file('templates/errorTemplateXML.xml');
            $res->error->code = $code;
            $res->error->msg = $GLOBALS['errorHash'][$code];

			header('Content-Type: text/xml');
			echo $res->asxml();
		}
		else
		{
			generateError(2500);
		}
    }
    elseif($GLOBALS['errorType'] == "JSON")
    {
        if (file_exists('templates/errorTemplateJSON.json'))
			{
				$res = json_decode(file_get_contents('templates/errorTemplateJSON.json'), true);

                $res['error']['code'] = $code;
                $res['error']['msg'] = $GLOBALS['errorHash'][$code];

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
        echo "Error in error reporting function";
    }
}
//Test for above function.
//$error = array ("ERROR","1500","Test");
//generateError($error, $type="XML");

//This function loops through the XML file finding a match based on the code, it then returns an array with the data for that node.
//The rate described is the rate vs the base currency.
//This function is included in this file since the inclusion of this file is required in all API files.
function findData($code, $xml)
{
    $node = array('code'=>"", 'name'=>"", 'loc'=>"", 'rate'=> 0, 'inactive'=>"");
    foreach($xml->rates->cur as $currency)
            {
                if($currency->code == $code)
                {
                    $node['code'] = $currency->code;
                    $node['name'] = $currency->name;
                    $node['loc'] = $currency->loc;
                    $node['rate'] = $currency->rate;
                    $node['inactive'] = $currency->inactive;
                }
            }
    return $node;
}
?>
