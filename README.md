### PHP Wrapper für PROFFIX REST-API

[![Build Status](https://travis-ci.org/pitwch/php-wrapper-proffix-restapi.svg?branch=master)](https://travis-ci.org/pitwch/php-wrapper-proffix-restapi)


### Installation
Der Wrapper kann entweder geklont via [Composer](https://getcomposer.org) oder als PHAR installiert werden.

```php
composer require pitwch/php-wrapper-proffix-restapi
```

#### Features

- Verwendet [phphttpclient](http://phphttpclient.com) für HTTP - Requests.
- Schreibt Logfiles nach /log
- Verwendet `PSR-0` autoload.

#### Variante 1: Verwendung mit Composer


Autoload RestAPIWrapperProffix class:

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;


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

Die Konfiguration wird dem Client mitgegeben:

| Konfiguration    | Beispiel                                                                         | Bemerkung                                        |
|------------------|----------------------------------------------------------------------------------|--------------------------------------------------|
| url              | https://myserver.ch:999                                                          | URL der REST-API **ohne pxapi/v2/**              |
| apiDatabase      | DEMO                                                                             | Name der Datenbank                               |
| apiUser          | USR                                                                              | Names des Benutzers                              |
| apiPassword      | b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017                 | SHA256-Hash des Benutzerpasswortes               |
| apiModule        | ADR,STU                                                                          | Benötigte Module (mit Komma getrennt)            |
| options          | array('key'=>'112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028') | Optionen (Details unter Optionen)                |


Beispiel:
```php

require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

$pxrest =  new  Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    array('key'=>'112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028','limit'=>2));
$adressen = $pxrest->get('ADR/Adresse',array('filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'','depth'=>1,'fields'=>'AdressNr,Name,GeaendertAm'));;
print_r($adressen);
```
### Optionen

Optionen sind **fakultativ** und werden in der Regel nicht benötigt:

| Option           | Beispiel                                                         | Bemerkung                                                      |
|------------------|------------------------------------------------------------------|----------------------------------------------------------------|
| key              | 112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028 | API-Key als SHA256 - Hash (kann auch direkt mitgegeben werden) |
| version          | v2                                                               | API-Version; Standard = v2                                     |
| api_prefix       | /pxapi/                                                          | Prefix für die API; Standard = /pxapi/                         |
| login_endpoint   | PRO/Login                                                        | Endpunkt für Login; Standard = PRO/Login                       |
| user_agent       | php-wrapper-proffix-restapi                                      | User Agent; Standard = php-wrapper-proffix-restapi             |
| timeout          | 15                                                               | Timeout für Curl in Sekunden; Standard = 15                    |
| follow_redirects | true                                                             | Weiterleitungen der API folgen; Standard = false               |

#### Methoden

Folgende unterschiedlichen Methoden sind mit dem Wrapper möglich:


##### Get / Query

```php
$pxrest =  new  Client(...)
$adresse = $pxrest->get("ADR/Adresse/1")  //Legt Response als Objects in $adresse ab
$adresse->Name //DEMO AG
```


##### Put / Update

```php
$pxrest =  new  Client(...)
$data = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $pxrest->put("ADR/Adresse",$data)  //Sendet $data an Endpunkt ADR/Adresse
```

##### Post / Create

```php
$pxrest =  new  Client(...)
$data = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $pxrest->post("ADR/Adresse",$data)  //Sendet $data an Endpunkt ADR/Adresse
```

##### Info

Ruft Infos vom Endpunkt **PRO/Info** ab.

```php
$pxrest =  new  Client(...)

//Variante 1: API - Key direkt mitgeben
$info1 = $pxrest->info('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');
  
//Variante 2: API - Key aus Options verwenden (sofern dort hinterlegt)
$info2 = $pxrest->info();
```

##### Datenbank

Ruft Infos vom Endpunkt **PRO/Datenbank** ab.

```php
$pxrest = new Client(...);

//Variante 1: API - Key direkt mitgeben
$datenbank1 = $pxrest->database('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');
  
//Variante 2: API - Key aus Options verwenden (sofern dort hinterlegt)
$datenbank2 = $pxrest->database();
  ```


### Weitere Beispiele

Im Ordner [/examples](https://github.com/pitwch/php-wrapper-proffix-restapi/tree/master/examples) finden sich weitere,
auskommentierte Beispiele.
