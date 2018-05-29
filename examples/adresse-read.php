<?

require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

$pxrest =  new  Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    array('key'=>'112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028','limit'=>1));


$adressen = $pxrest->get('ADR/Adresse',array('filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'','depth'=>1,'fields'=>'AdressNr,Name,PLZ,Ort'));;

echo $adressen->AdressNr;                   //  returns Name of Adress
echo "\n";                                  //  new line
echo $adressen->PLZ." ".$adressen->Ort;     //  returns PLZ + Ort of Adress