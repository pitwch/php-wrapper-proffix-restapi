<?php
require_once __DIR__ . '/../vendor/autoload.php';  //Subdirectory --> /../

use RestAPIWrapperProffix\RestAPIWrapperProffix;

$config = array(
    'api_user' => 'SP',
    'api_password' => 'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'api_database' => 'DEMO',
    'api_url' => 'https://restapi.myserver.ch:123/pxapi/v2/',
    'api_modules' => 'ADR'
);

$rest = new RestAPIWrapperProffix($config);
$read = $rest->Get("ADR/Adresse",'Vorname@="Max"'); // Query for all adresses which Vorame contains "Max"

foreach ($read as $adresse) {
    echo $adresse->Name." ".$adresse->Vorname."\n";                // Echo Name + Vorname + newline
}


/* Example Result
Muster Max
Müller Max
Meier Maximilian
Claudius Maximus