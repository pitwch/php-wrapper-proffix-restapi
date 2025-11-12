# Error Handling

## Overview

The PROFFIX REST API Wrapper provides enhanced error handling that captures both general error messages and field-level validation errors from the PROFFIX API.

## Field-Level Validation Errors

When the PROFFIX API returns validation errors, they typically include:

- A general `Message` describing the overall error
- A `Fields` array containing specific field validation errors

### Example PROFFIX API Error Response

```json
{
  "Fields": [
    {
      "Reason": "EMPTY",
      "Name": "PLZ",
      "Message": "PLZ darf nicht leer bleiben!"
    },
    {
      "Reason": "EMPTY",
      "Name": "Land",
      "Message": "Land darf nicht leer bleiben!"
    }
  ],
  "Message": "Mindestens ein Feld ist ungültig."
}
```

## Using HttpClientException

The `HttpClientException` class now provides methods to access detailed error information:

### Basic Usage

```php
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;

try {
    $response = $client->request('endpoint', 'POST', $data);
} catch (HttpClientException $e) {
    // Get the main error message
    echo $e->getMessage(); // "Mindestens ein Feld ist ungültig."
    
    // Get HTTP status code
    echo $e->getCode(); // e.g., 400
}
```

### Accessing Field-Level Errors

```php
try {
    $response = $client->request('endpoint', 'POST', $data);
} catch (HttpClientException $e) {
    // Check if field errors exist
    if ($e->hasFieldErrors()) {
        // Get array of field errors
        $fieldErrors = $e->getFieldErrors();
        
        foreach ($fieldErrors as $error) {
            echo "Field: " . $error['Name'] . "\n";
            echo "Message: " . $error['Message'] . "\n";
            echo "Reason: " . $error['Reason'] . "\n";
        }
    }
}
```

### Getting Detailed Error Message

```php
try {
    $response = $client->request('endpoint', 'POST', $data);
} catch (HttpClientException $e) {
    // Get a formatted message with all field errors
    echo $e->getDetailedMessage();
    
    /* Output:
     * Mindestens ein Feld ist ungültig.
     * Field errors:
     *   - PLZ: PLZ darf nicht leer bleiben! (Reason: EMPTY)
     *   - Land: Land darf nicht leer bleiben! (Reason: EMPTY)
     */
}
```

## Available Methods

### `hasFieldErrors(): bool`

Returns `true` if field-level validation errors are present.

### `getFieldErrors(): ?array`

Returns an array of field errors, where each error contains:

- `Name`: The field name
- `Message`: The validation error message
- `Reason`: The reason code (e.g., "EMPTY", "INVALID", etc.)

Returns `null` if no field errors exist.

### `getDetailedMessage(): string`

Returns a formatted string containing the main error message plus all field-level errors.

### `getMessage(): string`

Returns only the main error message (standard Exception method).

### `getCode(): int`

Returns the HTTP status code (standard Exception method).

### `getRequest(): Request`

Returns the Request object that caused the error.

### `getResponse(): ?Response`

Returns the Response object if available.

## Best Practices

1. **Always catch HttpClientException** when making API requests
2. **Check for field errors** using `hasFieldErrors()` before accessing them
3. **Use `getDetailedMessage()`** for logging to capture complete error context
4. **Use `getFieldErrors()`** when you need to process individual field errors programmatically
5. **Display user-friendly messages** by mapping field names and error reasons to localized text

## Example: User-Friendly Error Display

```php
try {
    $response = $client->request('Adresse', 'POST', $addressData);
} catch (HttpClientException $e) {
    if ($e->hasFieldErrors()) {
        $errors = [];
        foreach ($e->getFieldErrors() as $error) {
            $errors[$error['Name']] = $error['Message'];
        }
        
        // Display errors next to form fields
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'field_errors' => $errors
        ];
    }
    
    // Generic error without field details
    return [
        'success' => false,
        'message' => $e->getMessage()
    ];
}
```
