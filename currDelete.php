<?php
include_once "config.php";
include_once "generateError.php";

function respondDELETE($xml)
{
    $data = json_decode(file_get_contents('php://input', true), true);

    $code = $data['code'];
    $type = $data['type'];

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
				    generateError(2400);
                }
                else
                {
                    $currency->inactive = "TRUE";
                }
            }
        }

    $at = date('d/m/y h:i');
    
    //Send response to client.
    if($type == "XML")
		{
			
			if (file_exists('templates/delResXML.xml'))
			{
				
				$res = simplexml_load_file('templates/delResXML.xml');
				$res->at = date("d M y  h:i");
				$res->code = $code;

				header('Content-Type: text/xml');
				echo $res->asxml();
			}
			else
			{
				generateError(2500);
			}
		}
		elseif($type == "JSON")
		{
			if (file_exists('templates/delResJSON.json'))
			{
				$res = json_decode(file_get_contents('templates/delResJSON.json'), true);

				$res['delete']['at'] = date("d M y \ h:i");
                $res['delete']['code'] = (string)$code;
                
                header('Content-Type: text/json');
				$res = json_encode($res);

				echo $res;
			}
			else
			{
				generateError(2500);
			}
        }
        
    //Save the changes to the xml file, other functions of the api will check if the inactive flag and act accordingly.
    //This prevents the actual deletion of any data.
    file_put_contents('curData.xml', $xml->asxml());
    }
}
?>