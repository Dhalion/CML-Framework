<?php

namespace CML\Controllers;

use CML\Classes\Cache;

class ApiController
{

    private Cache $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function getRepoData($params)
    {

        $cacheData = $this->cache->getCacheData("api_data");

        if (!$cacheData) {
            $url = $params['url'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            $this->cache->setCacheData("api_data", $data, $this->cache::DAY_IN_SECONDS);
            return is_array($data) ? $data : false;
        }

        return $cacheData;
    }
}
