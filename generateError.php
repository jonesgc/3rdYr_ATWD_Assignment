<?php

include_once 'config.php';
//Purpose of this function is to send an error response to the client, the default format is XML, but an option for JSON.
//The code will generate an error string, which would be the error code it wants to throw.
//Note the errorHash global used in this function is found in config.php

function generateError($code, $type="XML")
{
    if($type == "XML")
    {
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<method type="'. $type.'">';
        echo "<error>";
        echo "<code>".$code."</code>";
        echo "<msg>". $GLOBALS['errorHash'][$code]."</msg>";
        echo "</error>";
        echo "</method>";
    }
    elseif($type == "JSON")
    {
        $res = array('method'=>$type, 'code'=>$code,'message'=>$GLOBALS['errorHash'][$code]);
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

?>