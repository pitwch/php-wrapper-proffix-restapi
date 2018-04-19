### PHP Wrapper für PROFFIX REST-API



### Installation
Der Wrapper kann entweder direkt heruntergeladen, geklont oder via [Composer](https://getcomposer.org) installiert werden.

```php
composer require pitw/php-wrapper-proffix-restapi
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

$client = new RestAPIWrapperProffix($api_user,$api_password,$api_database,$api_url,$api_modules,"",false);
$adresse = $client->Get("ADR/Adresse/1")
$adresse->Name //DEMO AG
```