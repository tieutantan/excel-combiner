<?php

use Phpfastcache\Helper\Psr16Adapter;

class Cache {

    private Psr16Adapter $cache;
    private int $expire = 9999;

    /**
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws ReflectionException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    function __construct() {
        $defaultDriver = 'Files';
        $this->cache = new Psr16Adapter($defaultDriver);
    }

    /**
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($key) {
        if (!empty($data = $this->cache->get($key)))
            return $data;

        return null;
    }

    /**
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($key, $value): bool
    {
        $minutes = 60 * $this->expire;
        $this->cache->set( $key, $value, $minutes);
        return true;
    }
}