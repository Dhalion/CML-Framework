<?php

namespace CML\Classes;

/**
 * Class Cache
 *
 * Cache provides methods for caching and retrieving HTML content.
 */
class Cache {
    use Functions\Functions;

    /**
     * Cache directory path
     */
    private string $cacheDir;

    public string $url;

    /**
     * Constructor
     *
     * @param string $cacheDir The directory path where cache files will be stored
     */
    public function __construct(string $cacheDir) {
        $this->cacheDir = self::getRootPath($cacheDir);
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $url = strtok(rtrim($_SERVER['REQUEST_URI'], '/'), '?');
        $url = str_replace(trim(self::assetUrl('/'), '/'), '', $url);
        $this->url = $url;
    }

    /**
     * Get cached HTML content if available
     *
     * @param string $cacheKey The key to identify the cached content
     * @return string|false Cached HTML content or false if not available
     */
    public function get(string $cacheKey) {
        $cacheFile = $this->getCacheFilePath($cacheKey);
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }
        return false;
    }

    /**
     * Cache HTML content
     *
     * @param string $cacheKey The key to identify the cached content
     * @param string $htmlContent The HTML content to be cached
     * @return bool True if caching is successful, false otherwise
     */
    public function set(string $cacheKey, string $htmlContent) {
        $cacheFile = $this->getCacheFilePath($cacheKey);
        return file_put_contents($cacheFile, $htmlContent) !== false;
    }

    /**
     * Get the cache file path for a given cache key
     *
     * @param string $cacheKey The cache key
     * @return string The cache file path
     */
    private function getCacheFilePath(string $cacheKey) {
        return $this->cacheDir . md5($cacheKey) . '.cache';
    }
}
