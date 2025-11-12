<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClient;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;

// Example configuration
$url = 'https://work.pitw.ch:1500';
$database = 'DEMODB';
$username = 'TM';
$password = '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4';
$modules = 'VOL';

try {
    $client = new HttpClient($url, $database, $username, $password, $modules);
    
    // Example: Making a request that might fail with field validation errors
    // This would typically be a POST/PUT request with invalid data
    $response = $client->request('some/endpoint', 'POST', [
        'PLZ' => '',  // Empty field that's required
        'Land' => '', // Empty field that's required
    ]);
    
} catch (HttpClientException $e) {
    echo "Error occurred:\n";
    echo "HTTP Code: " . $e->getCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n\n";
    
    // Check if there are field-level validation errors
    if ($e->hasFieldErrors()) {
        echo "Field validation errors detected:\n";
        
        // Get individual field errors
        $fieldErrors = $e->getFieldErrors();
        foreach ($fieldErrors as $error) {
            echo sprintf(
                "  - Field '%s': %s (Reason: %s)\n",
                $error['Name'],
                $error['Message'],
                $error['Reason']
            );
        }
        
        echo "\n";
        
        // Or get the complete detailed message
        echo "Detailed error message:\n";
        echo $e->getDetailedMessage() . "\n";
    }
}
