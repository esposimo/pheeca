<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\Cache\CacheInterface;
use \smn\pheeca\kernel\File;
use \smn\pheeca\kernel\Logger;

/**
 * Description of Cache
 *
 * @author Simone
 */
class Cache {

    /**
     * Può essere o opcache , o apc (sono gli unici due che compilano gli script php)
     * @var String 
     */
    protected static $_cached_scripts = 'opcache';

    /**
     * Può essere o apc o memcached , dato che sono gli unici due a poter memorizzare oggetti
     * @var String 
     */
    protected static $_cached_objects = 'memcache';

    /**
     * Lista degli adapters in base al sistema di cache
     * @var Array 
     */
    protected static $_adapters = array(
        'apc' => 'APC',
        'opcache' => 'OPCache',
        'memcache' => 'MemCacheServer'
    );
    protected static $_referenceClassScript;
    protected static $_referenceClassObject;

    /**
     * Imposta il nome dell'adapter per lo storage del bytecode degli script PHP
     * @param String $method
     */
    public static function setCachedScriptsStorageType($method = 'opcache') {
        Logger::info('Cambio il metodo di caching degli script in ' . $method, 'core');
        self::$_cached_scripts = $method;
    }

    /**
     * Restituisce il nome dell'adapter per lo storage del bytecode degli script PHP
     * @return type
     */
    public static function getCachedScriptsStorageType() {
        return self::$_cached_scripts;
    }

    /**
     * Configura le regole di storage del bytecode
     * @param type $refresh_rules
     */
    public static function configCacheScriptsCustom($refresh_rules = array()) {
        self::$_refresh_rules = $refresh_rules;
    }

    /**
     * Configura il nome dell'adapter per gestire la cache dei dati
     * @param type $method
     */
    public static function setCachedObjectsStorageType($method = 'apc') {
        Logger::info('Cambio il metodo di caching degli oggetti in ' . $method, 'core');
        self::$_cached_objects = $method;
    }

    /**
     * Restituisce il nome dell'adapter per gestire la cache dei dati
     * @return String
     */
    public static function getCachedObjectsStorageType() {
        return self::$_cached_objects;
    }

    /**
     * Restituisce il nome della classe relativa ad un adapter
     * @param String $name
     * @return boolean|String
     */
    private static function getAdaptersByName($name) {
        if (array_key_exists(strtolower($name), self::$_adapters)) {
            return self::$_adapters[strtolower($name)];
        }
        return false;
    }

    /**
     * Restituisce l'istanza della classe usata per storicizzare gli oggetti
     * @return \smn\pheeca\kernel\Cache\APC|\smn\pheeca\kernel\Cache\OPCache|\smn\pheeca\kernel\Cache\MemCacheServer
     */
    public static function getReferenceClassObject() {
        return self::$_referenceClassObject;
    }

    /**
     * Restituisce l'istanza della classe usata per storicizzare il bytecode degli script PHP
     * @return \smn\pheeca\kernel\Cache\APC|\smn\pheeca\kernel\Cache\OPCache|\smn\pheeca\kernel\Cache\MemCacheServer
     */
    public static function getReferenceClassScript() {
        return self::$_referenceClassScript;
    }

    /**
     * Crea i due adapter ai quali passa le opzioni
     * @param type $options
     */
    public static function initialize($options = array()) {

        $classnameObject = '\\' . __CLASS__ . '\\' . self::getAdaptersByName(self::$_cached_objects);
        self::$_referenceClassObject = new $classnameObject($options);

        $classnameScript = '\\' . __CLASS__ . '\\' . self::getAdaptersByName(self::$_cached_scripts);
        self::$_referenceClassScript = new $classnameScript($options);
    }

    public static function clearScripts() {
        return self::getReferenceClassScript()->clearScripts();
    }

    public static function clearCache() {
        return self::getReferenceClassObject()->clearCache();
    }

    public static function compileScripts() {
        return self::getReferenceClassScript()->compileScripts();
    }

    public static function delete($name, $type = null) {
        return self::getReferenceClassObject()->delete($name, $type);
    }

    public static function get($name, $type = null) {
        self::getReferenceClassObject()->get($name, $type);
    }

    public static function isCached($name, $type = null) {
        self::getReferenceClassObject()->isCached($name, $type);
    }

    public static function set($name, $value, $type = null, $ttl = 0) {
        return self::getReferenceClassObject()->set($name, $value, $type, $ttl);
    }

    public static function update($name, $value, $type = null, $ttl = 0) {
        return self::getReferenceClassObject()->update($name, $value, $type, $ttl);
    }

}
