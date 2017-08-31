<?php

namespace No\Cache;

use No\Cache;
use Cache\Memcached as Memcached;

class Memcache implements Cache {

    private $cache;

    function __construct() {
        $cache = new Memcached();
        $cache->addServer('127.0.0.1', 11211);
        $cache->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $cache->setOption(Memcached::OPT_COMPRESSION, true);
        $this->cache = $cache;
    }

    function load($key) {
        $ckey = md5($key);
        $data = $this->cache->get("no_{$ckey}");
        if ($data === false) {
            return false;
        }
        return $data;
    }

    function save($key, $data) {
        $ckey = md5($key);
        $this->cache->set("no_{$ckey}", $data,0);
    }

}
