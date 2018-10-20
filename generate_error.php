<?php

include_once 'config.php';
//Purpose of this function is to send an error response to the client, the default format is XML, but an option for JSON.
//The code will generate an error string, which would be the error code it wants to throw.
//Note the errorHash global used in this function is found in config.php

function generate_error($error, $type="XML")
{
    if($type == "XML")
    {
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<method type="'. $type.'">';
        echo "<error>";
        echo "<code>".$error[1]."</code>";
        echo "<msg>". $GLOBALS['errorHash'][$error[1]]."</msg>";
        echo "</error>";
        echo "</method>";

        
    }
    elseif($type == "JSON")
    {
        $res = array('method'=>$type, 'code'=>$error[1],'message'=>$GLOBALS['errorHash'][$error[1]]);
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
//generate_error($error, $type="XML");

?>