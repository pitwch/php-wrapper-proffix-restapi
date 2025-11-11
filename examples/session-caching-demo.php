<?php
/**
 * Session Caching Demo
 * 
 * This example demonstrates how session caching improves performance
 * by reusing authentication sessions across multiple script executions.
 */

require __DIR__ . '/../vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

// Load environment variables (adjust path as needed)
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

echo "=== Session Caching Demo ===\n\n";

// Example 1: Session caching enabled (default)
echo "1. First request with session caching enabled:\n";
$startTime = microtime(true);

$client1 = new Client(
    $_ENV['PROFFIX_API_URL'] ?? 'https://your-server.ch:1500',
    $_ENV['PROFFIX_API_DATABASE'] ?? 'DEMODB',
    $_ENV['PROFFIX_API_USERNAME'] ?? 'USR',
    $_ENV['PROFFIX_API_PASSWORD'] ?? 'your-sha256-hash',
    $_ENV['PROFFIX_API_MODULES'] ?? 'ADR',
    ['enable_session_caching' => true] // This is the default
);

try {
    $addresses = $client1->get('ADR/Adresse', ['limit' => 1]);
    $duration1 = microtime(true) - $startTime;
    echo "   ✓ Request completed in " . round($duration1 * 1000, 2) . "ms\n";
    echo "   ✓ Session cached for future use\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Second request using cached session
echo "2. Second request using cached session:\n";
$startTime = microtime(true);

$client2 = new Client(
    $_ENV['PROFFIX_API_URL'] ?? 'https://your-server.ch:1500',
    $_ENV['PROFFIX_API_DATABASE'] ?? 'DEMODB',
    $_ENV['PROFFIX_API_USERNAME'] ?? 'USR',
    $_ENV['PROFFIX_API_PASSWORD'] ?? 'your-sha256-hash',
    $_ENV['PROFFIX_API_MODULES'] ?? 'ADR',
    ['enable_session_caching' => true]
);

try {
    $addresses = $client2->get('ADR/Adresse', ['limit' => 1]);
    $duration2 = microtime(true) - $startTime;
    echo "   ✓ Request completed in " . round($duration2 * 1000, 2) . "ms\n";
    echo "   ✓ Used cached session (no login required)\n";
    
    if ($duration2 < $duration1) {
        $improvement = round((($duration1 - $duration2) / $duration1) * 100, 1);
        echo "   ✓ Performance improvement: ~{$improvement}%\n\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Session caching disabled
echo "3. Request with session caching disabled:\n";
$startTime = microtime(true);

$client3 = new Client(
    $_ENV['PROFFIX_API_URL'] ?? 'https://your-server.ch:1500',
    $_ENV['PROFFIX_API_DATABASE'] ?? 'DEMODB',
    $_ENV['PROFFIX_API_USERNAME'] ?? 'USR',
    $_ENV['PROFFIX_API_PASSWORD'] ?? 'your-sha256-hash',
    $_ENV['PROFFIX_API_MODULES'] ?? 'ADR',
    ['enable_session_caching' => false] // Disable caching
);

try {
    $addresses = $client3->get('ADR/Adresse', ['limit' => 1]);
    $duration3 = microtime(true) - $startTime;
    echo "   ✓ Request completed in " . round($duration3 * 1000, 2) . "ms\n";
    echo "   ✓ Session not cached (fresh login every time)\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Summary ===\n";
echo "Session caching reduces authentication overhead by reusing sessions.\n";
echo "This is especially beneficial for:\n";
echo "  - Scripts that run frequently (cron jobs, webhooks)\n";
echo "  - Applications with multiple requests\n";
echo "  - Reducing load on the PROFFIX server\n\n";

echo "Cache location:\n";
if (PHP_OS_FAMILY === 'Windows') {
    echo "  Windows: " . (getenv('APPDATA') ?: sys_get_temp_dir()) . "\\php-wrapper-proffix-restapi\\\n";
} else {
    echo "  Linux/Mac: " . (getenv('HOME') ? getenv('HOME') . '/.cache' : sys_get_temp_dir()) . "/php-wrapper-proffix-restapi/\n";
}
