<?php

include_once 'config.php';
//Purpose of this function is to send an error response to the client, the default format is XML, but an option for JSON.
//The code will generate an error string, which would be the error code it wants to throw.
//Note the errorHash global used in this function is found in config.php

function generateError($code)
{
    if($GLOBALS['errorType'] == "XML")
    {
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<method type="XML">';
        echo "<error>";
        echo "<code>".$code."</code>";
        echo "<msg>". $GLOBALS['errorHash'][$code]."</msg>";
        echo "</error>";
        echo "</method>";
    }
    elseif($GLOBALS['errorType'] == "JSON")
    {
        $res = array('method'=>'JSON', 'code'=>$code,'message'=>$GLOBALS['errorHash'][$code]);
        $res = json_encode($res);
        print_r($res);
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
