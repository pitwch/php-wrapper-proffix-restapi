<?php

require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

$pxrest =  new  Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    array('key'=>'112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028','limit'=>1));

$artikel = 'PC 6';                                                                       //Set Artikelnr with blank(!)
$bestand = $pxrest->get("LAG/Artikel/".rawurlencode($artikel)."/Bestand");      //Use rawurlencode() !

//Iterate through stocks array
$stockTotal = 0; 
foreach ($bestand as $stock) {
    $stocktotal = $stockTotal + $stock->Bestand;
}
echo "Bestand: ".$stockTotal;                                                             //Return combined stocks