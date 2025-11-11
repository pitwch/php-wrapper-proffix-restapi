# PHP Wrapper für PROFFIX REST-API

Ein effizienter PHP Wrapper für die PROFFIX REST-API.

![alt text](https://raw.githubusercontent.com/pitwch/php-wrapper-proffix-restapi/master/php-wrapper-proffix-rest.jpg "PHP Wrapper PROFFIX REST API")

## Installation

Der Wrapper kann via [Composer](https://getcomposer.org) installiert werden.

```php
composer require pitwch/rest-api-wrapper-proffix-php
```

## Konfiguration

### Initialisierung

Autoload der `RestAPIWrapperProffix` Klasse:

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;
```

Die Konfiguration wird dem Client mitgegeben:

| Konfiguration    | Beispiel                                                                         | Bemerkung                                        |
|------------------|----------------------------------------------------------------------------------|--------------------------------------------------|
| url              | `https://myserver.ch:999`                                                        | URL der REST-API **ohne pxapi/v2/**              |
| apiDatabase      | `DEMO`                                                                           | Name der Datenbank                               |
| apiUser          | `USR`                                                                            | Name des Benutzers                               |
| apiPassword      | `b62cce2fe18f7a156a9c...`                                                        | SHA256-Hash des Benutzerpasswortes               |
| apiModule        | `ADR,STU`                                                                        | Benötigte Module (mit Komma getrennt)            |
| options          | `array('key'=>'112a5a90...')`                                                    | Optionen (Details unter Optionen)                |

### Beispiel für die Initialisierung

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

$pxrest = new Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    ['key'=>'112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028','limit'=>2]
);

$adressen = $pxrest->get('ADR/Adresse', ['filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'', 'depth'=>1, 'fields'=>'AdressNr,Name,GeaendertAm']);
print_r($adressen);
```

## Optionen

Optionen sind **fakultativ** und werden in der Regel nicht benötigt:

| Option                  | Beispiel                               | Bemerkung                                                      |
|-------------------------|----------------------------------------|----------------------------------------------------------------|
| key                     | `112a5a90fe28b...`                     | API-Key als SHA256 - Hash (kann auch direkt mitgegeben werden) |
| version                 | `v2`                                   | API-Version; Standard = v2                                     |
| api_prefix              | `/pxapi/`                              | Prefix für die API; Standard = /pxapi/                         |
| login_endpoint          | `PRO/Login`                            | Endpunkt für Login; Standard = PRO/Login                       |
| user_agent              | `php-wrapper-proffix-restapi`          | User Agent; Standard = php-wrapper-proffix-restapi             |
| timeout                 | `15`                                   | Timeout für Curl in Sekunden; Standard = 15                    |
| follow_redirects        | `true`                                 | Weiterleitungen der API folgen; Standard = false               |
| enable_session_caching  | `true`                                 | Session-Caching aktivieren; Standard = true                    |

### Session-Caching

Der Wrapper unterstützt automatisches Session-Caching, um die Performance zu verbessern und die Anzahl der Login-Requests zu reduzieren. Das Session-Caching ist standardmässig **aktiviert**.

**Funktionsweise:**

- Nach einem erfolgreichen Login wird die `PxSessionId` in einer Datei gespeichert
- Bei nachfolgenden Requests wird zuerst versucht, die gespeicherte Session zu laden
- Bei ungültigen Sessions (401-Fehler) wird automatisch ein neuer Login durchgeführt
- Sessions werden automatisch beim Logout oder bei Fehlern gelöscht

**Cache-Speicherort:**

- **Windows:** `%APPDATA%/php-wrapper-proffix-restapi/`
- **Linux/Mac:** `~/.cache/php-wrapper-proffix-restapi/` oder `/tmp/php-wrapper-proffix-restapi/`

Der Dateiname wird aus Benutzername, Datenbank und URL generiert, um Konflikte bei mehreren Clients zu vermeiden.

**Session-Caching deaktivieren:**

```php
$pxrest = new Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    ['enable_session_caching' => false]
);
```

## Methoden

### Allgemeine Methoden (`get`, `put`, `post`, `delete`)

| Parameter  | Typ    | Bemerkung                                                                                                |
|------------|--------|----------------------------------------------------------------------------------------------------------|
| endpoint   | `string` | Endpunkt der PROFFIX REST-API; z.B. `ADR/Adresse`, `STU/Rapporte`...                                     |
| data       | `array`  | Daten (werden automatisch in JSON konvertiert); z.B: `["Name"=>"Demo AG",...]`                           |
| parameters | `array`  | Parameter gemäss [PROFFIX REST API Docs](http://www.proffix.net/Portals/0/content/REST%20API/index.html) |

*Sonderzeichen in den Parametern müssen gegebenfalls mit Escape-Zeichen verwendet werden, z.B:*

```php
// Escape ' with \'
$params = ['filter' => 'GeaendertAm>d\'2018-05-17 14:54:56\'', 'depth' => 1, 'fields' => 'AdressNr,Name,GeaendertAm'];
$pxrest->get('ADR/Adresse', $params);
```

#### Get / Query

```php
// Einfache Abfrage
$adresse = $pxrest->get("ADR/Adresse/1");
echo $adresse->Name; // DEMO AG

// Abfrage mit Parametern
$params = ['filter'=>'GeaendertAm>d\'2018-05-17 14:54:56\'','depth'=>1,'fields'=>'AdressNr,Name,GeaendertAm','limit'=>5];
$adressen = $pxrest->get("ADR/Adresse", $params);
```

#### Put / Update

```php
$data = ["AdressNr"=>1, "Ort"=>"Zürich", "PLZ"=>8000, "EMail"=>"test@test.com"];
$adresse = $pxrest->put("ADR/Adresse", $data);
```

#### Post / Create

```php
$data = ["Ort"=>"Zürich", "PLZ"=>8000, "EMail"=>"test@test.com"];
$neueAdresse = $pxrest->post("ADR/Adresse", $data);
```

#### Delete

```php
$response = $pxrest->delete("ADR/Adresse/42");
```

### Spezifische Methoden

#### `getList(int $listenr, array $body = [])`

Generiert eine PROFFIX-Liste (z.B. ein PDF) und gibt das Ergebnis als `Response`-Objekt zurück, welches den rohen Dateiinhalt enthält.

| Parameter | Typ      | Bemerkung                                                                                                                                      |
|-----------|----------|------------------------------------------------------------------------------------------------------------------------------------------------|
| `$listenr`| `int`    | Die `ListeNr` der Liste, die generiert werden soll.                                                                                            |
| `$body`   | `array`  | (Optional) Ein assoziatives Array mit Parametern für die Listengenerierung. **Wichtig:** Es muss mindestens ein leeres JSON-Objekt (`{}`) gesendet werden. |

*Beispiel:*

```php
$listeNr = 1029; // Beispiel-ID für ADR_Adressliste.repx
$pdfResponse = $pxrest->getList($listeNr);

if ($pdfResponse->getCode() === 200) {
    file_put_contents('Adressliste.pdf', $pdfResponse->getBody());
    echo "Liste erfolgreich als Adressliste.pdf gespeichert.";
}
```

## Spezielle Endpunkte

### Info

Ruft Infos vom Endpunkt `PRO/Info` ab.

*Hinweis: Dieser Endpunkt / Abfrage blockiert keine Lizenz.*

```php
// Variante 1: API-Key direkt mitgeben
$info1 = $pxrest->info('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');
  
// Variante 2: API-Key aus Options verwenden (sofern dort hinterlegt)
$info2 = $pxrest->info();
```

### Datenbank

Ruft Infos vom Endpunkt `PRO/Datenbank` ab.

```php
$dbInfo = $pxrest->database();
```

## Response / Antwort

Alle Methoden geben die Response als Array bzw. `NULL` (z.B. bei `DELETE`) zurück. Bei Fehlern wird eine `HttpClientException` mit der Rückmeldung der PROFFIX REST-API geworfen.

Zudem lassen sich Zusatzinformationen zur letzten Response wie folgt ausgeben:

### Letzter Request

```php
$lastRequest = $pxrest->http->getRequest();
$lastRequest->getUrl(); // Get requested URL (string).
$lastRequest->getMethod(); // Get request method (string).
$lastRequest->getParameters(); // Get request parameters (array).
$lastRequest->getHeaders(); // Get request headers (array).
$lastRequest->getBody(); // Get request body (JSON).
```

### Letzte Response

```php
$lastResponse = $pxrest->http->getResponse();
$lastResponse->getCode(); // Response code (int).
$lastResponse->getHeaders(); // Response headers (array).
$lastResponse->getBody(); // Response body (JSON).
```

## Ausnahmen / Spezialfälle

* Endpunkte, welche Leerschläge enthalten (z.B. `LAG/Artikel/PC 7/Bestand`), müssen mit `rawurlencode()` genutzt werden.

## Weitere Beispiele

Im Ordner [/examples](https://github.com/pitwch/php-wrapper-proffix-restapi/tree/master/examples) finden sich weitere auskommentierte Beispiele.

## Weitere Wrapper für die Proffix Rest-API

* [Golang Wrapper für die Proffix Rest-API](https://github.com/pitwch/go-wrapper-proffix-restapi)
* [Dart Wrapper für die Proffix Rest-API](https://github.com/pitwch/dart_proffix_rest)
