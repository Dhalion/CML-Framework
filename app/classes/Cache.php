<?php

namespace CML\Classes;

/**
 * Class Cache
 *
 * Cache provides methods for caching and retrieving HTML content.
 */
abstract class Cache {
    use Functions\Functions;

    public bool $cacheEnabled = false;

    /**
     * Cache directory path
     */
    private string $cacheDir;

    public array $cacheOptions;

    /**
     * Enables or disables caching and sets the cache options.
     *
     * @param string $clearCurrentQuery The query to clear the current cache.
     * @param string $clearAllQuery The query to clear all cache.
     * @param bool $cacheEnabled Whether caching is enabled or not.
     */
    public function cache(string $clearCurrentQuery, string $clearAllQuery, bool $cacheEnabled = false) {
        $this->cacheEnabled = $cacheEnabled;
        if($this->cacheEnabled){
            $this->initCache();
            $this->cacheOptions['config']['clearCurrent'] = $clearCurrentQuery;
            $this->cacheOptions['config']['clearAll'] = $clearAllQuery;
        }
    }

    /**
     * Initializes the cache directory.
     */
    private function initCache() {
        $this->cacheDir = self::getRootPath(CACHE_PATH);
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Get cached HTML content if available
     *
     * @param string $cacheKey The key to identify the cached content
     * @return string|false Cached HTML content or false if not available
     */
    protected function getCache(string $cacheKey) {
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
    protected function setCache(string $cacheKey, string $htmlContent) {
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
        return $this->cacheDir . base64_encode($cacheKey) . '.cache';
    }

    /**
     * Purges all cache files in the cache directory.
     */
    protected function purgeAll(){
        $cacheFiles = glob($this->cacheDir . '*.cache');
        foreach ($cacheFiles as $cacheFile) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }

    /**
     * Purges the cache file associated with the given cache key.
     *
     * @param string $cacheKey The cache key.
     */
    protected function purgeCache(string $cacheKey){
        $cacheFile = $this->getCacheFilePath($cacheKey);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}