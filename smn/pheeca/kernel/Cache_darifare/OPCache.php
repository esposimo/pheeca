<?php

namespace smn\pheeca\kernel\Cache;

use \smn\pheeca\kernel\Logger;
use \smn\pheeca\kernel\File;

/**
 * Description of OPCache
 *
 * @author Simone
 */
class OPCache implements CacheInterface {

    protected $_refresh_rules = array(
        'bytecode' => array(
            'dir' => RENDER_KERNEL_PATH,
            'extension' => 'php',
            'recursive' => true
        )
    );

    public function __construct($options = array()) {
        if (array_key_exists('bytecode', $options)) {
            $this->_refresh_rules = $options['bytecode'];
        }
    }

    public function compileScripts() {
        if (!extension_loaded('Zend OPcache')) {
            return;
        }
        $files = array();
        foreach ($this->_refresh_rules as $rules) {
            $rules = (object) $rules;
            $files = array_merge($files, File::getFilesFromDirectory($rules->dir, $rules->extension, $rules->recursive));
        }
        foreach ($files as $file) {
            if (!\opcache_is_script_cached($file)) {
                // questo ciclo dovrebbe farlo solo allo start del ws, dato che una volta che sono tutti cacheati, controlla lui
                // se ci sono modifiche da fare dopo i secondi opcache.revalidate_freq passati dalla memorizzazione alla request
                Logger::debug('Ho compilato lo script ' . $file . ' perchè non era compilato', RENDER_CORE_LOGNAME);
                opcache_compile_file($file);
            }
        }
    }

    public function clearCache() {
        return null;
    }

    public function clearScripts() {
        \opcache_reset();
    }

    public function initialize($options = array()) {
        return false;
    }

    public function delete($name, $type = null) {
        return null;
    }

    public function get($name, $type = null) {
        return null;
    }

    public function isCached($name, $type = null) {
        return null;
    }

    public function set($name, $value, $type = null, $ttl = 0) {
        return null;
    }

    public function update($name, $value, $type = null, $ttl = 0) {
        return null;
    }

}
