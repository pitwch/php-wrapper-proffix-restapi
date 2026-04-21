# PHP Wrapper für PROFFIX REST-API

Ein effizienter PHP Wrapper für die PROFFIX REST-API (V4).

![alt text](https://raw.githubusercontent.com/pitwch/php-wrapper-proffix-restapi/master/php-wrapper-proffix-rest.jpg "PHP Wrapper PROFFIX REST API")

> **Für KI-Assistenten / AI Code Generation:** Dieser Wrapper kapselt die PROFFIX REST-API vollständig. Der Login erfolgt **automatisch** — kein manueller Login-Aufruf nötig. Das `options`-Array ist vollständig **optional** und kann weggelassen oder leer übergeben werden. Passwörter werden als **SHA256-Hash** übergeben.

---

## Installation

```bash
composer require pitwch/rest-api-wrapper-proffix-php
```

---

## Schnellstart (Minimal-Beispiel)

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

// Minimale Initialisierung — options ist optional und kann weggelassen werden
$pxrest = new Client(
    'https://myserver.ch:999', // URL ohne /pxapi/V4/
    'DEMO',                    // Datenbankname
    'USR',                     // Benutzername
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017', // SHA256-Passwort
    'ADR,STU'                  // Benötigte Module (kommagetrennt)
    // options: weggelassen = alle Defaults aktiv
);

// Login passiert automatisch beim ersten Request
$adressen = $pxrest->get('ADR/Adresse', ['limit' => 5]);
print_r($adressen);
```

---

## Konstruktor / Initialisierung

```php
new Client(string $url, string $apiDatabase, string $apiUser, string $apiPassword, string|array $apiModules, array $options = [])
```

| Parameter      | Typ              | Pflicht | Beispiel                                       | Bemerkung                                      |
|----------------|------------------|---------|------------------------------------------------|------------------------------------------------|
| `$url`         | `string`         | ✅      | `https://myserver.ch:999`                      | URL **ohne** `/pxapi/V4/`                      |
| `$apiDatabase` | `string`         | ✅      | `DEMO`                                         | Datenbankname                                  |
| `$apiUser`     | `string`         | ✅      | `USR`                                          | Benutzername                                   |
| `$apiPassword` | `string`         | ✅      | `b62cce2fe18f7a...`                            | SHA256-Hash des Passworts                      |
| `$apiModules`  | `string\|array`  | ✅      | `'ADR,STU'` oder `['ADR', 'STU']`              | Benötigte Module                               |
| `$options`     | `array`          | ❌      | `[]`                                           | Leer oder weggelassen = alle Defaults          |

**Wichtig:** `$options` kann vollständig weggelassen **oder** als leeres Array `[]` übergeben werden — der Client funktioniert in beiden Fällen korrekt.

---

## Options-Referenz

Alle Optionen sind **fakultativ**. Standardwerte gelten automatisch.

| Option                 | Typ      | Default                          | Bemerkung                                                         |
|------------------------|----------|----------------------------------|-------------------------------------------------------------------|
| `key`                  | `string` | `''`                             | API-Key (SHA256). Nur nötig für `info()`/`database()` Endpunkte   |
| `version`              | `string` | `'V4'`                           | API-Version                                                       |
| `api_prefix`           | `string` | `'/pxapi/'`                      | URL-Prefix                                                        |
| `login_endpoint`       | `string` | `'PRO/Login'`                    | Login-Endpunkt                                                    |
| `user_agent`           | `string` | `'php-wrapper-proffix-restapi'`  | HTTP User-Agent                                                   |
| `timeout`              | `int`    | `15`                             | cURL Timeout in Sekunden                                          |
| `verify_ssl`           | `bool`   | `true`                           | SSL-Zertifikat prüfen                                             |
| `follow_redirects`     | `bool`   | `false`                          | HTTP-Weiterleitungen folgen                                       |
| `enable_session_caching` | `bool` | `true`                           | Session-ID zwischenspeichern (reduziert Login-Requests)           |
| `cache_dir`            | `string` | plattformspezifisch              | Eigenes Cache-Verzeichnis (nützlich bei `open_basedir`)           |

---

## CRUD-Methoden

### GET — Lesen

```php
// Einzelner Datensatz
$adresse = $pxrest->get('ADR/Adresse/1');
echo $adresse->Name; // DEMO AG

// Mit Filtern und Feldselektion
$adressen = $pxrest->get('ADR/Adresse', [
    'filter' => 'GeaendertAm>d\'2018-05-17 14:54:56\'',
    'depth'  => 1,
    'fields' => 'AdressNr,Name,PLZ,Ort',
    'limit'  => 5,
]);

// Filter: enthält "Max" (PROFFIX @= Operator)
$adressen = $pxrest->get('ADR/Adresse', ['filter' => 'Vorname@="Max"']);
foreach ($adressen as $adresse) {
    echo $adresse->Name . ' ' . $adresse->Vorname . "\n";
}
```

### POST — Erstellen

```php
$data = ['Ort' => 'Zürich', 'PLZ' => 8000, 'EMail' => 'test@test.com'];
$neueAdresse = $pxrest->post('ADR/Adresse', $data);
```

### PUT — Aktualisieren

```php
$data = ['AdressNr' => 1, 'Ort' => 'Zürich', 'PLZ' => 8000, 'EMail' => 'test@test.com'];
$pxrest->put('ADR/Adresse', $data);
```

### DELETE — Löschen

```php
$pxrest->delete('ADR/Adresse/42');
```

---

## Spezielle Methoden

### `getList(int $listenr, array $body = [])` — Liste/PDF generieren

Generiert eine PROFFIX-Liste und gibt den rohen Dateiinhalt zurück.

```php
$listeNr = 1029; // ListeNr aus PROFFIX
$pdfResponse = $pxrest->getList($listeNr);

if ($pdfResponse->getCode() === 200) {
    file_put_contents('Adressliste.pdf', $pdfResponse->getBody());
}
```

### `info(string $key = '')` — Server-Info (ohne Lizenz)

```php
// Mit API-Key direkt
$info = $pxrest->info('112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028');

// API-Key aus options['key'] verwenden
$info = $pxrest->info();
```

### `database(string $key = '')` — Datenbank-Info

```php
$dbInfo = $pxrest->database();
```

---

## Sonderfälle

### Leerzeichen in Endpunkten

```php
// Artikelnummer mit Leerzeichen → rawurlencode() verwenden
$artikel = 'PC 7';
$bestand = $pxrest->get('LAG/Artikel/' . rawurlencode($artikel) . '/Bestand');
```

### Module als Array oder String

```php
// Beide Varianten sind gleichwertig:
'ADR,STU'           // String mit Komma
['ADR', 'STU']      // Array
```

---

## Session-Caching

Der Wrapper speichert die `PxSessionId` nach dem Login automatisch zwischen. Dadurch entfällt bei wiederholten Script-Aufrufen der Login-Overhead.

**Standard-Speicherorte** (ohne `cache_dir` Option):

- **Windows:** `%APPDATA%/php-wrapper-proffix-restapi/`
- **Linux/Mac:** `~/.cache/php-wrapper-proffix-restapi/` oder `/tmp/php-wrapper-proffix-restapi/`

### Custom Cache-Verzeichnis (z.B. bei `open_basedir`)

```php
// /tmp/ — typisch für Shared-Hosting (Plesk etc.)
$pxrest = new Client('https://myserver.ch:999', 'DEMO', 'USR', 'sha256hash', 'ADR', [
    'cache_dir' => '/tmp/proffix-cache',
]);

// Cross-Platform empfohlen
$pxrest = new Client('https://myserver.ch:999', 'DEMO', 'USR', 'sha256hash', 'ADR', [
    'cache_dir' => sys_get_temp_dir() . '/proffix-cache',
]);

// Innerhalb des Projekts
$pxrest = new Client('https://myserver.ch:999', 'DEMO', 'USR', 'sha256hash', 'ADR', [
    'cache_dir' => __DIR__ . '/cache',
]);
```

### Session-Caching deaktivieren

```php
$pxrest = new Client('https://myserver.ch:999', 'DEMO', 'USR', 'sha256hash', 'ADR', [
    'enable_session_caching' => false,
]);
```

**Cache-Verzeichnis-Priorität:**

1. `cache_dir` Option (wenn gesetzt)
2. Plattformspezifisches Standardverzeichnis
3. Fallback: `sys_get_temp_dir()`

---

## Fehlerbehandlung

Alle Methoden werfen bei Fehlern eine `HttpClientException`. Diese enthält neben der HTTP-Statuscode auch feldspezifische Validierungsfehler der PROFFIX API.

### Beispiel PROFFIX Fehlerantwort

```json
{
  "Fields": [
    { "Reason": "EMPTY", "Name": "PLZ",  "Message": "PLZ darf nicht leer bleiben!" },
    { "Reason": "EMPTY", "Name": "Land", "Message": "Land darf nicht leer bleiben!" }
  ],
  "Message": "Mindestens ein Feld ist ungültig."
}
```

### Exception abfangen

```php
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;

try {
    $neueAdresse = $pxrest->post('ADR/Adresse', ['Ort' => 'Zürich', 'PLZ' => '', 'Land' => '']);
} catch (HttpClientException $e) {
    echo $e->getMessage();   // "Mindestens ein Feld ist ungültig."
    echo $e->getCode();      // HTTP-Statuscode, z.B. 400

    if ($e->hasFieldErrors()) {
        foreach ($e->getFieldErrors() as $error) {
            echo sprintf("  - %s: %s (Grund: %s)\n", $error['Name'], $error['Message'], $error['Reason']);
        }
        // Oder als formatierte Nachricht:
        echo $e->getDetailedMessage();
    }
}
```

### Exception-Methoden

| Methode                | Rückgabe  | Beschreibung                                          |
|------------------------|-----------|-------------------------------------------------------|
| `getMessage()`         | `string`  | Hauptfehlermeldung                                    |
| `getCode()`            | `int`     | HTTP-Statuscode                                       |
| `hasFieldErrors()`     | `bool`    | Prüft ob Feldvalidierungsfehler vorhanden             |
| `getFieldErrors()`     | `?array`  | Array mit `Name`, `Message`, `Reason` pro Feld        |
| `getDetailedMessage()` | `string`  | Formatierte Gesamtfehlermeldung inkl. Feldfehler      |
| `getRequest()`         | `Request` | Request-Objekt                                        |
| `getResponse()`        | `?Response` | Response-Objekt                                     |

---

## Request / Response Debugging

```php
// Letzter Request
$req = $pxrest->getHttpClient()->getRequest();
$req->getUrl();        // Vollständige URL (string)
$req->getMethod();     // HTTP-Methode (string)
$req->getParameters(); // Query-Parameter (array)
$req->getHeaders();    // HTTP-Header (array)
$req->getBody();       // Request-Body als JSON (string)

// Letzte Response
$res = $pxrest->getHttpClient()->getResponse();
$res->getCode();       // HTTP-Statuscode (int)
$res->getHeaders();    // Response-Header (array)
$res->getBody();       // Response-Body als JSON (string)
```

---

## Vollständiges Beispiel mit allen Optionen

```php
require __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;

$pxrest = new Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'b62cce2fe18f7a156a9c719c57bebf0478a3d50f0d7bd18d9e8a40be2e663017',
    'ADR,STU',
    [
        'key'                    => '112a5a90fe28b23ed2c776562a7d1043957b5b79fad242b10141254b4de59028',
        'verify_ssl'             => true,
        'timeout'                => 15,
        'enable_session_caching' => true,
        'cache_dir'              => sys_get_temp_dir() . '/proffix-cache',
    ]
);

try {
    // GET mit Filter
    $adressen = $pxrest->get('ADR/Adresse', [
        'filter' => 'GeaendertAm>d\'2018-05-17 14:54:56\'',
        'fields' => 'AdressNr,Name,PLZ,Ort',
        'limit'  => 10,
    ]);

    // POST — neue Adresse erstellen
    $neueAdresse = $pxrest->post('ADR/Adresse', [
        'Name' => 'Muster AG',
        'Ort'  => 'Zürich',
        'PLZ'  => 8000,
    ]);

    // PUT — bestehende Adresse aktualisieren
    $pxrest->put('ADR/Adresse', ['AdressNr' => 1, 'Ort' => 'Bern', 'PLZ' => 3000]);

    // DELETE
    $pxrest->delete('ADR/Adresse/42');

    // PDF-Liste generieren
    $pdf = $pxrest->getList(1029);
    file_put_contents('liste.pdf', $pdf->getBody());

} catch (HttpClientException $e) {
    echo $e->getDetailedMessage();
}
```

---

## Weitere Beispiele

Im Ordner [/examples](https://github.com/pitwch/php-wrapper-proffix-restapi/tree/master/examples) finden sich weitere Beispiele:

- `adresse-read.php` — Einfache GET-Abfrage
- `adresse-read-filter.php` — GET mit Filtern
- `adresse-update.php` — POST/PUT
- `artikel-bestand.php` — rawurlencode für Leerzeichen
- `error_handling_example.php` — Fehlerbehandlung
- `session-caching-demo.php` — Session-Caching Performance-Demo
- `example-custom-cache.php` — Custom Cache-Verzeichnisse

---

## Weitere Wrapper für die Proffix Rest-API

- [Golang Wrapper für die Proffix Rest-API](https://github.com/pitwch/go-wrapper-proffix-restapi)
- [Dart Wrapper für die Proffix Rest-API](https://github.com/pitwch/dart_proffix_rest)
