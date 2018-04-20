<?php
require_once __DIR__ . '/../vendor/autoload.php';  //Subdirectory --> /../

use RestAPIWrapperProffix\RestAPIWrapperProffix;

$config = array(
    'api_user' => 'SP',
    'api_password' => 'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'api_database' => 'DEMO',
    'api_url' => 'https://restapi.myserver.ch:123/pxapi/v2/',
    'api_modules' => 'ADR',
    'api_key' => '112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028',
    'enable_log' => true,
    'log_path' => ''
);

$rest = new RestAPIWrapperProffix($config);
$login = $rest->Get("ADR/Adresse/1");

echo $login->Name;                          //  returns Name of Adress 1
echo "\n";                                  //  new line
echo $login->PLZ." ".$login->Ort;           //  returns PLZ + Ort of Adress 1