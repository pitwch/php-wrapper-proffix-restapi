

# PHP Wrapper für PROFFIX REST-API

Ein effizienter PHP Wrapper für die PROFFIX REST-API

![alt text](https://raw.githubusercontent.com/pitwch/php-wrapper-proffix-restapi/master/php-wrapper-proffix-rest.jpg "PHP Wrapper PROFFIX REST API")

### Installation
Der Wrapper kann entweder geklont oder via [Composer](https://getcomposer.org) installiert werden.

```php
composer require pitwch/rest-api-wrapper-proffix-php
```


#### Variante 1: Verwendung mit Composer (empfohlen)


Autoload RestAPIWrapperProffix class:

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

```


#### Konfiguration

Die Konfiguration wird dem Client mitgegeben:

| Konfiguration    | Beispiel                                                                         | Bemerkung                                        |
|------------------|----------------------------------------------------------------------------------|--------------------------------------------------|
| url              | https://myserver.ch:999                                                          | URL der REST-API **ohne pxapi/v2/**              |
| apiDatabase      | DEMO                                                                             | Name der Datenbank                               |
| apiUser          | USR                                                                              | Names des Benutzers                              |
| apiPassword      | b62cce2fe18f7a156a9c...0f0d7bd18d9e8a40be2e663017                                | SHA256-Hash des Benutzerpasswortes               |
| apiModule        | ADR,STU                                                                          | Benötigte Module (mit Komma getrennt)            |
| options          | array('key'=>'112a5a90...59028')                                                 | Optionen (Details unter Optionen)             |


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
| key              | 112a5a90fe28b...242b10141254b4de59028 | API-Key als SHA256 - Hash (kann auch direkt mitgegeben werden) |
| version          | v2                                                               | API-Version; Standard = v2                                     |
| api_prefix       | /pxapi/                                                          | Prefix für die API; Standard = /pxapi/                         |
| login_endpoint   | PRO/Login                                                        | Endpunkt für Login; Standard = PRO/Login                       |
| user_agent       | php-wrapper-proffix-restapi                                      | User Agent; Standard = php-wrapper-proffix-restapi             |
| timeout          | 15                                                               | Timeout für Curl in Sekunden; Standard = 15                    |
| follow_redirects | true                                                             | Weiterleitungen der API folgen; Standard = false               |

#### Methoden


| Parameter  | Typ    | Bemerkung                                                                                                |
|------------|--------|----------------------------------------------------------------------------------------------------------|
| endpoint   | string | Endpunkt der PROFFIX REST-API; z.B. ADR/Adresse,STU/Rapporte...                                          |
| data       | array  | Daten (werden automatisch in JSON konvertiert); z.B: array("Name"=>"Demo AG",...)                        |
| parameters | array  | Parameter gemäss [PROFFIX REST API Docs](http://www.proffix.net/Portals/0/content/REST%20API/index.html) |


*Sonderzeichen in den Parametern müssen gegebenfalls mit Escape-Zeichen verwendet werden, z.B:*

```php
//Escape ' with \'
array('filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'','depth'=>1,'fields'=>'AdressNr,Name,GeaendertAm')
```


Folgende unterschiedlichen Methoden sind mit dem Wrapper möglich:



##### Get / Query

```php
//Einfache Abfrage
$pxrest =  new  Client(...)
$adresse = $pxrest->get("ADR/Adresse/1")  //Legt Response als Objects in $adresse ab
$adresse->Name //DEMO AG

/Abfrage mit Parametern
$pxrest =  new  Client(...)
$adresse = $pxrest->get("ADR/Adresse",array('filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'','depth'=>1,'fields'=>'AdressNr,Name,GeaendertAm','limit'=>5))

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


##### Response / Antwort

Alle Methoden geben die Response als Array bzw. NULL (z.B. bei DELETE)
Bei Fehlern wird `HttpClientException` mit Rückmeldung der PROFFIX REST-API ausgegeben.

Zudem lassen sich Zusatzinformationen zur Response wie folgt ausgeben:

```php
$pxrest =  new  Client(...)
$adresse = $pxrest->get("ADR/Adresse")

//Zusatzinformationen zum letzten Request
$lastRequest = $pxrest->http->getRequest();
$lastRequest->getUrl(); // Get requested URL (string).
$lastRequest->getMethod(); // Get request method (string).
$lastRequest->getParameters(); // Get request parameters (array).
$lastRequest->getHeaders(); // Get request headers (array).
$lastRequest->getBody(); // Get request body (JSON).


//Zusatzinformationen zur letzten Response
$lastResponse = $pxrest->http->getResponse();
$lastResponse->getCode(); // Response code (int).
$lastResponse->getHeaders(); // Response headers (array).
$lastResponse->getBody(); // Response body (JSON).
```


#### Spezielle Endpunkte


##### Info

Ruft Infos vom Endpunkt **PRO/Info** ab.

*Hinweis: Dieser Endpunkt / Abfrage blockiert keine Lizenz*

```php
$pxrest =  new  Client(...)

//Variante 1: API - Key direkt mitgeben
$info1 = $pxrest->info('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');
  
//Variante 2: API - Key aus Options verwenden (sofern dort hinterlegt)
$info2 = $pxrest->info();
```

##### Datenbank

Ruft Infos vom Endpunkt **PRO/Datenbank** ab.

*Hinweis: Dieser Endpunkt / Abfrage blockiert keine Lizenz*

```php
$pxrest = new Client(...);

//Variante 1: API - Key direkt mitgeben
$datenbank1 = $pxrest->database('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');
  
//Variante 2: API - Key aus Options verwenden (sofern dort hinterlegt)
$datenbank2 = $pxrest->database();
  ```
### Ausnahmen / Spezialfälle

* Endpunkte welche Leerschläge enthalten können (z.B. LAG/Artikel/PC 7/Bestand) müssen mit rawurlencode() genutzt werden

### Weitere Beispiele

Im Ordner [/examples](https://github.com/pitwch/php-wrapper-proffix-restapi/tree/master/examples) finden sich weitere,
auskommentierte Beispiele.


# Weitere Wrapper für die Proffix Rest-API

- [Golang Wrapper für die Proffix Rest-API](https://github.com/pitwch/go-wrapper-proffix-restapi) :link:
- [Dart Wrapper für die Proffix Rest-API](https://github.com/pitwch/dart_proffix_rest) :link:
