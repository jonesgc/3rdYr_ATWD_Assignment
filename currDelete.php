<?php
include_once "config.php";
include_once "generateError.php";

function respondDELETE($xml)
{
    $code = file_get_contents('php://input', true);

    $node = findData($code, $xml);
    
    //Check if the input code exists within the xml file.
    if(!$node['code'] == $code)
    {
        generateError(2400);
    }
    else
    {
        //Iterate through XML looking for code, then set the inative flag to TRUE.
        foreach ($xml->rates->cur as $currency)
        {
            $code = (string)$currency->name;
            $rate = (string)$currency->rate;

            if($node['code'] == $code)
            {
			    if($currency->inactive == "TRUE")
			    {
				    echo "Currency is already inactive.";
                }
                $currency->inactive = "FALSE";
            }
        }
    $at = date('d/m/y h:i');
    
    //Send response to client.
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<method type = "DELETE">';
    echo    '<at>'.$at.'</at>';
    echo    '<code>'.$code.'</code>';
    echo '</method>';

    //Save the changes to the xml file, other functions of the api will check if the inactive flag and act accordingly.
    //This prevents the actual deletion of any data.
    file_put_contents('curData.xml', $xml->asxml());
    }
}
?>