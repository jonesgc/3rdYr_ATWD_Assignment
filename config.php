<?php

$apiID = "08c94e1539cd46c69ef98a7a2a94ca7a";
$apiURL = "https://openexchangerates.org";
$apiLatest = "https://openexchangerates.org/api/latest.json?app_id=";
$base = "USD";
$errorHash = array(1000=>"Required parameter is missing", 
    1100 => "Parameter not recognized", 
    1200 => "Currency type not recognized",
    1300 => "Currency amount must be a decimal number",
    1400 => "Format must be xml or json",
    1500 => "Error in service",
    2000 => "Method not recognized or is missing",
    2100 => "Rate in wrong format or is missing",
    2200 => "Currency code in wrong format or is missing",
    2300 => "Country name in wrong format or is missing",
    2400 => "Currency code not found for update",
    2500 => "Error in service");
$GLOBALS['errorHash'] = $errorHash;
?>
