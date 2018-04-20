### PHP Wrapper für PROFFIX REST-API



### Installation
Der Wrapper kann entweder direkt heruntergeladen, geklont oder via [Composer](https://getcomposer.org) installiert werden.

```php
composer require pitwch/php-wrapper-proffix-restapi
```

#### Features

- Verwendet [phphttpclient](http://phphttpclient.com) für HTTP - Requests.
- Schreibt Logfiles nach /log
- Verwendet `PSR-0` autoload.

#### Verwendung


Autoload RestAPIWrapperProffix class:

```php
require_once __DIR__ . '/vendor/autoload.php';

use RestAPIWrapperProffix\RestAPIWrapperProffix;
```

Anschliessend kann die `RestAPIWrapperProffix` Klasse für die weitere Verwendung genutzt werden.

Beispiel:
```php
$api_user = "SP";
$api_password = "b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017";
$api_database = "DEMO";
$api_url = "https://restapi.myserver.ch:123/pxapi/v2/";
$api_modules = "ADR,FIB,DEB";
$api_key = "112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028";
$logpath = "";

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,$api_key,$logpath,false);
$adresse = $client->Get("ADR/Adresse/1")
$adresse->Name //DEMO AG
```

#### Methoden

Folgende unterschiedlichen Methoden sind mit dem Wrapper möglich:


##### Get / Query

```php

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$adresse = $client->Get("ADR/Adresse/1")  //Legt Response als Objects in $client ab
$adresse->Name //DEMO AG
```

##### Put / Update

```php

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$putdata = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $client->Update("ADR/Adresse",$putdata)  //Sendet $putdata an Endpunkt ADR/Adresse
```

##### Post / Create

```php

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$postdata = array("AdressNr"=>1,"Ort"=>"Zürich","PLZ"=>8000,"EMail"=>"test@test.com");
$adresse = $client->Create("ADR/Adresse",$postdata)  //Sendet $postdata an Endpunkt ADR/Adresse
```

##### GetInfo

Ruf Infos vom Endpunkt **PRO/Info** ab.

```php

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$info1 = $client->GetInfo()  //Sofern der API - Key in der Konfiguration hinterlegt ist
$info2 = $client->GetInfo("112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028")  //Der API - Key kann auch separat gesendet werden

```

##### GetDatabases

Ruf Infos vom Endpunkt **PRO/Datenbank** ab.

```php

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$datenbank1 = $client->GetDatabases()  //Sofern der API - Key in der Konfiguration hinterlegt ist
$datenbank2 = $client->GetDatabases("112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028")  //Der API - Key kann auch separat gesendet werden

```