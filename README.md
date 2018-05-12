### PHP Wrapper für PROFFIX REST-API

[![Build Status](https://travis-ci.org/pitwch/php-wrapper-proffix-restapi.svg?branch=master)](https://travis-ci.org/pitwch/php-wrapper-proffix-restapi)


### Installation
Der Wrapper kann entweder geklont via [Composer](https://getcomposer.org) oder als PHAR installiert werden.

```php
composer require pitwch/rest-api-wrapper-proffix-php
```

#### Features

- Verwendet [phphttpclient](http://phphttpclient.com) für HTTP - Requests.
- Schreibt Logfiles nach /log
- Verwendet `PSR-0` autoload.

#### Variante 1: Verwendung mit Composer


Autoload RestAPIWrapperProffix class:

```php
require_once __DIR__ . '/vendor/autoload.php';

use RestAPIWrapperProffix\RestAPIWrapperProffix;

$client = new RestAPIWrapperProffix($config);

```

#### Variante 2: Verwendung als PHAR

Den aktuellsten Build als PHAR gibt es jeweils hier:

[Download als PHAR](https://github.com/pitwch/php-wrapper-proffix-restapi/releases/latest)

**Wichtig:** Die Schlüsseldatei **PhpWrapperProffix.phar.pubkey** muss ebenfalls heruntergeladen werden und ins selbe Verzeichnis wie die  **PhpWrapperProffix.phar** kopiert werden!

```php
include("PhpWrapperProffix.phar");

$client = new \RestAPIWrapperProffix\RestAPIWrapperProffix($config);
```

#### Konfiguration

Die Konfiguration ($config) erfolgt über ein Array mit folgenden Werten:

 Key          | Beispiel                                                         | Bemerkung                                               |
|--------------|------------------------------------------------------------------|---------------------------------------------------------|
| api_user     | SP                                                               | Benutzername                                            |
| api_password | b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017 | Passwort als SHA256 - Hash                              |
| api_url      | DEMO                                                             | Datenbankname                                           |
| api_modules  | ADR,FIB,DEB                                                      | Benötigte Module mit Komma getrennt                     |
| api_key      | 112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028 | Fakultativ: API-Key als SHA256 - Hash                   |
| enable_log   | true                                                             | Fakultativ: Log aktivieren                              |
| log_path     | /../../log/demo.log                                              | Fakultativ: Pfad der Log-Files. Standard im Ordner /log |


Anschliessend kann die `RestAPIWrapperProffix` Klasse für die weitere Verwendung genutzt werden.

Beispiel:
```php

$config = array(
    'api_user' => 'SP',
    'api_password' => 'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'api_database' => 'DEMO',
    'api_url' => 'https://restapi.myserver.ch:123/pxapi/v2/',
    'api_modules' => 'ADR,FIB,DEB',
    'api_key' => '112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028',
    'enable_log' => true,
    'log_path' => ''
);
$client = new RestAPIWrapperProffix($config);
$adresse = $client->Get("ADR/Adresse/1")
$adresse->Name //DEMO AG
```

#### Methoden

Folgende unterschiedlichen Methoden sind mit dem Wrapper möglich:


##### Get / Query

```php
$client = new RestAPIWrapperProffix($config);
$adresse = $client->Get("ADR/Adresse/1")  //Legt Response als Objects in $client ab
$adresse->Name //DEMO AG
```

###### Filter
Soll bei einem GET-Request ein Filter verwendet werden, 
kann dieser als zweiter Parameter übergeben werden (ohne "?filter=").

Der Filter wird dann automatisch URL-encodiert.

```php
$client = new RestAPIWrapperProffix($config);
$adressefilter = $client->Get("ADR/Adresse",'Name@="Max"')      // Mit Filter
$adressefilter[0]->Name                                         //Muster AG
```

##### Put / Update

```php
$client = new RestAPIWrapperProffix($config);
$data = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $client->Update("ADR/Adresse",$data)  //Sendet $data an Endpunkt ADR/Adresse
```

##### Post / Create

```php
$client = new RestAPIWrapperProffix($config);
$data = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $client->Create("ADR/Adresse",$data)  //Sendet $data an Endpunkt ADR/Adresse
```

##### GetInfo

Ruf Infos vom Endpunkt **PRO/Info** ab.

```php
$client = new RestAPIWrapperProffix($config);
$info1 = $client->GetInfo()  //Sofern der API - Key in der Konfiguration hinterlegt ist
$info2 = $client->GetInfo("112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028")  //Der API - Key kann auch separat gesendet werden
```

##### GetDatabases

Ruf Infos vom Endpunkt **PRO/Datenbank** ab.

```php
$client = new RestAPIWrapperProffix($config);
$datenbank1 = $client->GetDatabases()  //Sofern der API - Key in der Konfiguration hinterlegt ist
$datenbank2 = $client->GetDatabases("112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028")  //Der API - Key kann auch separat gesendet werden
```


### Weitere Beispiele

Im Ordner [/examples](https://github.com/pitwch/php-wrapper-proffix-restapi/tree/master/examples) finden sich weitere,
auskommentierte Beispiele.
