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
        $this->cacheDir = self::getRootPath(cml_config('CACHE_PATH'));
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
    protected function setCache(string $cacheKey, string $htmlContent): bool {
        $cacheFile = $this->getCacheFilePath($cacheKey);
        return file_put_contents($cacheFile, $htmlContent) !== false;
    }

    /**
     * Get the cache file path for a given cache key
     *
     * @param string $cacheKey The cache key
     * @return string The cache file path
     */
    private function getCacheFilePath(string $cacheKey): string {
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

    /**
     * Sets cache data with a given name, value, and expiration time.
     *
     * @param string $name The name of the cache data.
     * @param mixed $value The value of the cache data.
     * @param int $expiration The expiration time of the cache data in seconds.
     * @return bool Returns true if the cache data was successfully set, false otherwise.
     */
    public function setCacheData(string $name, $value, int $expiration): bool {
        $transient_directory = self::getRootPath('cache/transient/');
        if (!is_dir($transient_directory)) {
            mkdir($transient_directory, 0755, true); 
        }
        
        $transient_file = $transient_directory . 'transients.temp'; 
        $transients = array();
        
        if (file_exists($transient_file)) {
            $data = file_get_contents($transient_file);
            $transients = unserialize($data);
        }
        
        $transients[$name] = array(
            'value' => $value,
            'expiration' => time() + $expiration
        );
        
        $data = serialize($transients);
        file_put_contents($transient_file, $data);
        
        return true;
    }

    /**
     * Retrieves data from the cache based on the given name.
     *
     * @param string $name The name of the cache data to retrieve.
     * @return mixed|false The value of the cache data if found and not expired, false otherwise.
     */
    public function getCacheData(string $name) {
        $transient_directory = self::getRootPath('cache/transient/');
        $transient_file = $transient_directory . 'transients.temp';
        
        if (!file_exists($transient_file)) {
            return false;
        }
        
        $data = file_get_contents($transient_file);
        $transients = unserialize($data);
        
        if (!isset($transients[$name])) {
            return false;
        }
        
        $transient = $transients[$name];
        
        if (isset($transient['expiration']) && $transient['expiration'] < time()) {
            unset($transients[$name]);
            $data = serialize($transients);
            file_put_contents($transient_file, $data);
            return false;
        }
        
        return $transient['value']; 
    }

    /**
     * Deletes a specific cache data by name.
     *
     * @param string $name The name of the cache data to delete.
     * @return bool Returns true if the cache data was successfully deleted, false otherwise.
     */
    public function deleteCacheData(string $name): bool {
        $transient_directory = self::getRootPath('cache/transient/');
        $transient_file = $transient_directory . 'transients.temp';
        
        if (!file_exists($transient_file)) {
            return false;
        }
        
        $data = file_get_contents($transient_file);
        $transients = unserialize($data);
        
        if (isset($transients[$name])) {
            unset($transients[$name]);
            $data = serialize($transients);
            file_put_contents($transient_file, $data);
            return true;
        }

        return false;
    }
}