<?php

use Phpfastcache\Helper\Psr16Adapter;

class Cache {

    private $cache;

    function __construct() {
        $defaultDriver = 'Files';
        $this->cache = new Psr16Adapter($defaultDriver);
    }

    public function get($key) {
        if (!empty($data = $this->cache->get($key)))
            return $data;

        return null;
    }

    public function set($key, $value, $minutes): bool
    {
        $minutes = 60 * $minutes;
        $this->cache->set( $key, $value, $minutes);
        return true;
    }
}