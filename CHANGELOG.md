# Changelog

Alle wichtigen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

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
