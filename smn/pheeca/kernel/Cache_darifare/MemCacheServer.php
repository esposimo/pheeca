<?php

namespace smn\pheeca\kernel\Cache;

use \smn\pheeca\kernel\Cache\CacheInterface;

/**
 * Description of MemCache
 *
 * @author Simone
 */
class MemCacheServer implements CacheInterface {

    /**
     *
     * @var type 
     */
    protected $_defaultOptions = array(
        'host' => 'localhost',
        'port' => 11211,
        'timeout' => 5,
        'persistent' => true,
        'weight' => 1,
        'retry_interval' => -1,
        'status' => true,
        'failure_callback' => null
    );
    protected $_options = array();
    protected $_global_index = 'global_index_session';

    /**
     *
     * @var type \Memcache
     */
    protected static $_memcache_resource;

    private static function isEnable() {
        if (extension_loaded('memcache')) {
            return true;
        }
        return false;
    }

    /**
     * Aggiorna l'indice globale dove sono presenti tutte le chiavi dei dati della cache
     * @param type $name
     * @param type $type 
     * @param type $action Può essere add o delete
     */
    private function refreshCacheIndex() {
        // va sempre verificato in fase di refresh se l'item esiste per davvero, perchè non posso verificare le chiavi con ttl diverso da 0
        // potrei, salvandomi sempre nell'indice globale anche il ttl, ma sempre dovrei ciclare per vedere quelli scaduti (tipo gc) tanto vale
        // verificare al refresh
        // mi prendo il global session
        // lo scompongo da json ad array
        $data = $this->getAllKeysInCache(); // indice delle chiavi
        //
        // se non ci sono elementi, non faccio nulla
        if (count($data) == 0) {
            return;
        }


        // altrimenti mi scorro l'array e per ogni indice, controllo se esiste ancora
        // se non esiste, eseguo l'azione



        $keys = array_keys(self::$_memcache_resource->get($data)); // mi darà tutte le chiave esistenti in cache tra quelle presenti negli indici


        $nocached_keys = array_diff($data, $keys); // il risultato mi darà tutte le chiavi non presenti più nella cache (quindi anche quelle che hanno superato il ttl)
        // a questo punto, cancello tutte le chiavi in $cached_keys , e cancello anche le chiavi dall'indice prima di ri-storicizzarlo
        foreach ($nocached_keys as $nck) {
            $this->_memcache_resource->delete($nck);
        }
        self::$_memcache_resource->set($this->_global_index, base64_encode(json_encode($keys, JSON_FORCE_OBJECT)), 0, 0);
    }

    private function addKeysToIndex($key) {
        $data = ($this->getAllKeysInCache()) ? $this->getAllKeysInCache() : array();
        if (array_search($key, $data) === false) {
            $data[] = $key;
        }
        self::$_memcache_resource->set($this->_global_index, base64_encode(json_encode($data, JSON_FORCE_OBJECT)), 0, 0);
        $this->refreshCacheIndex();
    }

    private function delKeysToIndex($key) {
        $data = ($this->getAllKeysInCache()) ? $this->getAllKeysInCache() : array();
        if (array_search($key, $data) !== false) {
            unset($data[$key]);
        }
        $this->_memcache_resource->set($this->_global_index, base64_encode(json_encode($data, JSON_FORCE_OBJECT)), 0, 0);
        $this->refreshCacheIndex();
    }

    public function __construct($options = array()) {
        if (!self::isEnable()) {
            return false;
        }
        if (!self::$_memcache_resource instanceof \Memcache) {
            self::$_memcache_resource = new \Memcache();
            $this->_options = $options;
            foreach ($options as $option) {
                extract(array_merge($this->_defaultOptions, $option), EXTR_OVERWRITE);
                self::$_memcache_resource->addServer($host, $port, $persistent, $weight, $timeout, $retry_interval, $status, $failure_callback);
                self::$_memcache_resource->setCompressThreshold(8192, 0.2);
                if ($persistent === true) {
                    self::$_memcache_resource->pconnect($host, $port, $timeout);
                } else {
                    self::$_memcache_resource->connect($host, $port, $timeout);
                }
            }
        }
    }

    /**
     * Memcache non compila scripts
     * @return boolean
     */
    public function compileScripts() {
        return false;
    }

    public function get($name, $type = null) {
        if (!self::isEnable()) {
            return false;
        }
        if (is_null($type)) {
            $type = 'data';
        }
        if (is_array($name)) {
            array_walk($name, function(&$value, $item, $content) {
                $type = $content[0];
                $value = sprintf('cache_%s_%s', $type, $value);
            }, array($type));
            // se il conteggio delle chiavi fetch è uguale a quello delle chiavi richieste, allora restituisco true
            return $this->_memcache_resource->get($name);
        }
        $cache_key = sprintf('cache_%s_%s', $type, $name);
        return self::$_memcache_resource->get($cache_key);
    }

    public function set($name, $value, $type = null, $ttl = 0) {
        if (!self::isEnable()) {
            return false;
        }
        if (is_null($type)) {
            $type = 'data';
        }
        $cache_key = sprintf('cache_%s_%s', $type, $name);
        $return = self::$_memcache_resource->set($cache_key, $value, false, $ttl);
        $this->addKeysToIndex($cache_key);
        return $return;
    }

    public function isCached($name, $type = null) {
        if (!self::isEnable()) {
            return false;
        }
        if (!$this->get($name, $type)) {
            return false;
        }
        return true;
    }

    public function update($name, $value, $type = null, $ttl = 0) {
        if (!self::isEnable()) {
            return false;
        }
        if (is_null($type)) {
            $type = 'data';
        }
        $return = $this->set($name, $value, $type, $ttl);
        $this->addKeysToIndex($name);
        return $return;
    }

    public function delete($name, $type = null) {
        if (!self::isEnable()) {
            return false;
        }
        if (is_null($type)) {
            $type = 'data';
        }
        $cache_key = sprintf('cache_%s_%s', $type, $name);
        $return = $this->_memcache_resource->delete($cache_key);
        $this->delKeysToIndex($cache_key);
        return $return;
    }

    public function clearCache() {
        if (!self::isEnable()) {
            return false;
        }
        $this->_memcache_resource->flush();
    }

    public function clearScripts() {
        return false;
    }

    public function getServers() {
        $stats = $this->_memcache_resource->getextendedstats();
        $servers = array();
        foreach ($stats as $server => $info) {
            array_push($servers, $server);
        }
        return $servers;
    }

    public function getAllKeysInCache() {
        $json = base64_decode(self::$_memcache_resource->get($this->_global_index));
        return (json_decode($json, true)) ? json_decode($json, true) : array();
    }

}
