<?php

namespace CML\Classes;

/**
 * Class Cache
 *
 * Cache provides methods for caching and retrieving HTML content.
 */
abstract class Cache {
    use Functions\Functions;

    /**
     * Cache directory path
     */
    private string $cacheDir;

    protected array $cacheConfig = array();

    public function initCache() {
        $this->cacheDir = self::getRootPath(CACHE_PATH);
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Sets the cache configuration.
     *
     * @param array $config The cache configuration array.
     */
    public function cacheConfig(array $config){
        $this->cacheConfig = $config;
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
        return $this->cacheDir . md5($cacheKey) . '.cache';
    }

    protected function purgeAll(){
        $cacheFiles = glob($this->cacheDir . '*.cache');
        foreach ($cacheFiles as $cacheFile) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }

    protected function purgeCache(string $cacheKey){
        $cacheFile = $this->getCacheFilePath($cacheKey);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    protected function checkConfig(string $configKey){
        if(isset($_GET[$this->cacheConfig[$configKey]]) && isset($this->cacheConfig[$configKey]) && $this->cacheConfig[$configKey] == $_GET[$this->cacheConfig[$configKey]]){
            return true;
        }
    }
}
