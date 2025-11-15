# Changelog

Alle wichtigen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [2.2.0] - 2025-11-15

### Hinzugefügt

- **Eigene Cache-Verzeichnisse**: Neue Option `cache_dir` ermöglicht die Angabe eines eigenen Cache-Verzeichnisses für Session-Dateien
  - Löst Probleme mit `open_basedir`-Einschränkungen in Shared-Hosting-Umgebungen (z.B. Plesk)
  - Unterstützt beliebige Verzeichnisse: `/tmp/`, `sys_get_temp_dir()`, oder projektspezifische Pfade
  - Vollständig rückwärtskompatibel - bestehender Code funktioniert ohne Änderungen
- **Dokumentation**: Umfassende Dokumentation der `cache_dir`-Option im README
  - Beispiele für verschiedene Hosting-Umgebungen
  - Cache-Verzeichnis-Priorität erklärt
  - Empfohlene Konfigurationen für Plesk und andere Shared-Hosting-Anbieter
- **Beispiele**: Neue Datei `example-custom-cache.php` mit 5 verschiedenen Anwendungsbeispielen
- **Technische Dokumentation**: `CACHE_DIR_FIX.md` mit detaillierter Implementierungsdokumentation

### Geändert

- `SessionCache::__construct()` akzeptiert jetzt optionalen `$customCacheDir`-Parameter
- `SessionCache::getCacheDir()` priorisiert eigenes Cache-Verzeichnis über plattformspezifische Defaults
- `Options::getCacheDir()` neue Methode zum Abrufen der `cache_dir`-Option
- `HttpClient` übergibt `cache_dir` aus Optionen an `SessionCache`
- README aktualisiert mit `cache_dir`-Dokumentation und Anwendungsbeispielen

### Behoben

- Session-Cache funktioniert jetzt in Umgebungen mit `open_basedir`-Einschränkungen
- Keine PHP-Warnungen mehr bei eingeschränkten Dateisystem-Zugriffen

### Technische Details

**Cache-Verzeichnis-Priorität:**

1. Eigenes Verzeichnis (wenn `cache_dir` Option gesetzt)
2. Plattformspezifische Standard-Verzeichnisse
3. Fallback auf `/tmp/` oder `sys_get_temp_dir()`

**Verwendungsbeispiel:**

```php
$pxrest = new Client(
    'https://myserver.ch:999',
    'DEMO',
    'USR',
    'password',
    'ADR,STU',
    [
        'enable_session_caching' => true,
        'cache_dir' => sys_get_temp_dir() . '/proffix-cache'
    ]
);
```

## [2.1.0] - 2025-11-12

### Hinzugefügt

- **Erweitertes Error Handling**: Feldspezifische Validierungsfehler werden jetzt erfasst und können über die `HttpClientException` abgerufen werden
  - Neue Methode `hasFieldErrors()`: Prüft, ob feldspezifische Fehler vorhanden sind
  - Neue Methode `getFieldErrors()`: Gibt ein Array mit allen Feldfehlern zurück
  - Neue Methode `getDetailedMessage()`: Gibt eine formatierte Nachricht mit allen Fehlerdetails zurück
  - Feldvalidierungsfehler enthalten `Name`, `Message` und `Reason` für jeden Fehler
- **Dokumentation**: Umfassende Error-Handling-Dokumentation in `docs/ERROR_HANDLING.md`
- **Beispiele**: Neues Beispiel `examples/error_handling_example.php` zur Demonstration der Error-Handling-Features
- **SessionCache-Dokumentation**: Technische Details zur `SessionCache`-Klasse im README hinzugefügt

### Geändert

- `HttpClientException` erweitert um optionalen `$fieldErrors`-Parameter
- `HttpClient::lookForErrors()` extrahiert jetzt `Fields`-Array aus PROFFIX API Fehlerantworten
- README aktualisiert mit Error-Handling-Sektion und SessionCache-Details

### Technische Details

Die PROFFIX REST API gibt bei Validierungsfehlern ein `Fields`-Array zurück, das bisher ignoriert wurde. Jetzt werden diese Details erfasst und können programmatisch abgerufen werden:

```php
try {
    $response = $client->post('ADR/Adresse', $data);
} catch (HttpClientException $e) {
    if ($e->hasFieldErrors()) {
        foreach ($e->getFieldErrors() as $error) {
            echo "{$error['Name']}: {$error['Message']}\n";
        }
    }
}
```

## [2.0.0] - 2024

### Hinzugefügt

- Session-Caching-Funktionalität zur Performance-Verbesserung
- `SessionCache`-Klasse für dateibasiertes Session-Management
- Automatische Session-Wiederverwendung über mehrere Requests
- Plattformspezifische Cache-Verzeichnisse (Windows/Linux/Mac)
- Option `enable_session_caching` zum Aktivieren/Deaktivieren des Cachings

### Geändert

- PHP-Mindestversion auf 8.2 erhöht
- `HttpClient` nutzt jetzt Session-Caching standardmäßig
- Automatische Cache-Invalidierung bei 401-Fehlern

## [1.x.x] - Frühere Versionen

Siehe Git-Historie für Details zu früheren Versionen.
