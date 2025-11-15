<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;

/**
 * File-backed cache for storing PxSessionId.
 *
 * Cache directory priority:
 * 1. Custom directory provided via constructor (if specified)
 * 2. Platform-specific defaults:
 *    - On Windows: %APPDATA%/php-wrapper-proffix-restapi
 *    - On Linux/Mac: ~/.cache/php-wrapper-proffix-restapi or /tmp/php-wrapper-proffix-restapi
 *
 * The filename is derived from username, database, and restURL to avoid collisions
 * when multiple clients are used.
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class SessionCache
{
    private string $username;
    private string $database;
    private string $restURL;
    private ?string $cacheDir = null;
    private ?string $customCacheDir = null;

    /**
     * SessionCache constructor.
     *
     * @param string $username
     * @param string $database
     * @param string $restURL
     * @param string|null $customCacheDir Optional custom cache directory path
     */
    public function __construct(string $username, string $database, string $restURL, ?string $customCacheDir = null)
    {
        $this->username = $username;
        $this->database = $database;
        $this->restURL = $restURL;
        $this->customCacheDir = $customCacheDir;
    }

    /**
     * Load the cached session ID.
     *
     * @return string|null The cached session ID or null if not found/expired
     */
    public function load(): ?string
    {
        try {
            $file = $this->getCacheFile();
            if (!file_exists($file)) {
                return null;
            }

            $content = file_get_contents($file);
            if ($content === false || empty($content)) {
                return null;
            }

            return trim($content);
        } catch (\Exception $e) {
            // Silently ignore errors
            return null;
        }
    }

    /**
     * Save the session ID to cache.
     *
     * @param string $sessionId
     * @return bool True on success, false on failure
     */
    public function save(string $sessionId): bool
    {
        try {
            $dir = $this->getCacheDir();
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0700, true) && !is_dir($dir)) {
                    return false;
                }
            }

            $file = $this->getCacheFile();
            return file_put_contents($file, $sessionId, LOCK_EX) !== false;
        } catch (\Exception $e) {
            // Silently ignore errors
            return false;
        }
    }

    /**
     * Clear the cached session ID.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool
    {
        try {
            $file = $this->getCacheFile();
            if (file_exists($file)) {
                return unlink($file);
            }
            return true;
        } catch (\Exception $e) {
            // Silently ignore errors
            return false;
        }
    }

    /**
     * Get the cache file path.
     *
     * @return string
     */
    private function getCacheFile(): string
    {
        $dir = $this->getCacheDir();
        $safeName = $this->generateSafeName();
        return $dir . DIRECTORY_SEPARATOR . $safeName . '.session';
    }

    /**
     * Get the cache directory path.
     *
     * @return string
     */
    private function getCacheDir(): string
    {
        if ($this->cacheDir !== null) {
            return $this->cacheDir;
        }

        // Priority 1: Use custom cache directory if provided
        if ($this->customCacheDir !== null && !empty($this->customCacheDir)) {
            $this->cacheDir = rtrim($this->customCacheDir, DIRECTORY_SEPARATOR);
            return $this->cacheDir;
        }

        // Priority 2: Use platform-specific defaults
        if (PHP_OS_FAMILY === 'Windows') {
            $appData = getenv('APPDATA');
            if ($appData && !empty($appData)) {
                $this->cacheDir = $appData . DIRECTORY_SEPARATOR . 'php-wrapper-proffix-restapi';
            } else {
                $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-wrapper-proffix-restapi';
            }
        } else {
            // Linux/Mac: prefer ~/.cache, fallback to /tmp
            $home = getenv('HOME');
            if ($home && !empty($home)) {
                $this->cacheDir = $home . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'php-wrapper-proffix-restapi';
            } else {
                $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-wrapper-proffix-restapi';
            }
        }

        return $this->cacheDir;
    }

    /**
     * Generate a safe filename based on username, database, and URL.
     *
     * @return string
     */
    private function generateSafeName(): string
    {
        $identifier = $this->username . '|' . $this->database . '|' . $this->restURL;
        // Use base64url encoding (URL-safe base64)
        return rtrim(strtr(base64_encode($identifier), '+/', '-_'), '=');
    }
}
