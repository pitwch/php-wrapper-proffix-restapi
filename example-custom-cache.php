<?php
/**
 * Example: Using custom cache directory to avoid open_basedir restrictions
 * 
 * This example shows how to specify a custom cache directory that complies
 * with your hosting environment's open_basedir restrictions.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Pitwch\RestAPIWrapperProffix\Client;

// Example 1: Using /tmp/ directory (commonly allowed by open_basedir)
$options1 = [
    'enable_session_caching' => true,
    'cache_dir' => '/tmp/proffix-cache'
];

$client1 = new Client(
    'https://work.pitw.ch',
    'DEMODB',
    'TM',
    'your-password',
    '',
    $options1
);

// Example 2: Using sys_get_temp_dir() (cross-platform)
$options2 = [
    'enable_session_caching' => true,
    'cache_dir' => sys_get_temp_dir() . '/proffix-cache'
];

$client2 = new Client(
    'https://work.pitw.ch',
    'DEMODB',
    'TM',
    'your-password',
    '',
    $options2
);

// Example 3: Using a custom directory within your web root
$options3 = [
    'enable_session_caching' => true,
    'cache_dir' => __DIR__ . '/cache'
];

$client3 = new Client(
    'https://work.pitw.ch',
    'DEMODB',
    'TM',
    'your-password',
    '',
    $options3
);

// Example 4: Backward compatibility - no custom cache_dir (uses default behavior)
$options4 = [
    'enable_session_caching' => true
];

$client4 = new Client(
    'https://work.pitw.ch',
    'DEMODB',
    'TM',
    'your-password',
    '',
    $options4
);

// Example 5: Disable session caching entirely
$options5 = [
    'enable_session_caching' => false
];

$client5 = new Client(
    'https://work.pitw.ch',
    'DEMODB',
    'TM',
    'your-password',
    '',
    $options5
);

echo "✅ All client configurations created successfully!\n";
echo "Cache directories:\n";
echo "  - Client 1: /tmp/proffix-cache\n";
echo "  - Client 2: " . sys_get_temp_dir() . "/proffix-cache\n";
echo "  - Client 3: " . __DIR__ . "/cache\n";
echo "  - Client 4: (default platform-specific)\n";
echo "  - Client 5: (caching disabled)\n";
