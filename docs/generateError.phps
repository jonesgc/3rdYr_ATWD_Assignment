<?php

include_once 'config.php';
//Purpose of this function is to send an error response to the client, the default format is XML, but an option for JSON.
//The code will generate an error string, which would be the error code it wants to throw.
//Note the errorHash global used in this function is found in config.php

function generateError($code)
{   
    //Check the type of error type, then fetch the template that corrisponds to the value and output the error.
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
    $query = '//currencies/rates/cur[./code="'.$code.'"]';
    $match = $xml->xpath($query);

    if(empty($match))
    {
        return FALSE;
    }
    else
    {
        //Prepare return array.
        $node['code'] = $match[0]->code;
        $node['name'] = $match[0]->name;
        $node['loc'] = $match[0]->loc;
        $node['rate'] = $match[0]->rate;
        $node['inactive'] = $match[0]->inactive;
        return $node;
    }
    
}
//Test for findData.
/*
$testCode = "USD";
$test = simplexml_load_file('curData.xml'); 
$node = findData($testCode, $test);
print_r($node);
*/
?>
