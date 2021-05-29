<?php
namespace Core\System;

use Memcache;

class MemcachedController{

    private static $instance;
    private static $cacheExpireTime = 1; //seconds
    private $cache;

    private function __construct(){
        $this->cache = new Memcache;
        $cacheConfig = Config::config("memcached");
        $this->cache->connect($cacheConfig['host'],$cacheConfig['port']);
    }
    private function __clone(){}

    public static function getInstance()
    {
        if(!self::$instance){
           self::$instance = new MemcachedController();
        }

        return self::$instance;
    }

    public function checkKeyExist(String $key):bool{
        $val = $this->cache->get($key);
        return false;
    }

    public function save(String $key,$value){
        $this->cache->set($key,serialize($value),0,self::$cacheExpireTime);
    }

    public function get(String $key){
        return unserialize($this->cache->get($key));
    }
}