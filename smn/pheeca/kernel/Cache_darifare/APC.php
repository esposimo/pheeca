<?php

namespace smn\pheeca\kernel\Cache;

use \smn\pheeca\kernel\File;
use \smn\pheeca\kernel\Cache\CacheInterface;
use \smn\pheeca\kernel\Logger;

class APC implements CacheInterface {

    protected $_refresh_rules = array(
        'bytecode' => array(
            'dir' => RENDER_KERNEL_PATH,
            'extension' => 'php',
            'recursive' => true
        )
    );

    private static function isEnable() {
        if (extension_loaded('apc')) {
            return true;
        }
        return false;
    }

    private function isFileCached($file) {
        $cache_info = \apc_cache_info();
        $cache_list = $cache_info['cache_list'];


        foreach ($cache_list as $bytecode_file) {
            $filename = $bytecode_file['filename'];
            if (File::getFileUnixFormat($file) == File::getFileUnixFormat($filename)) {
                return true;
            }
        }
        return false;
    }

    public function __construct($options = array()) {
        if (array_key_exists('bytecode', $options)) {
            $this->_refresh_rules = $options['bytecode'];
        }
    }

    public function compileScripts() {
        if (!extension_loaded('apc')) {
            return;
        }
        $files = array();
        foreach ($this->_refresh_rules as $rules) {
            $rules = (object) $rules;
            $files = array_merge($files, File::getFilesFromDirectory($rules->dir, $rules->extension, $rules->recursive));
        }
        foreach ($files as $file) {
            if (!$this->isFileCached($file)) {
                if ($d = \apc_delete_file($file)) {
                    Logger::debug('Elimino dalla cache lo script ' . $file . ' per ricompilarlo', RENDER_CORE_LOGNAME);
                } else {
                    Logger::debug('Non sono riuscito ad eliminare ' . $file . ' ' . print_r($d, true));
                }

                if ($d = \apc_compile_file($file)) {
                    Logger::debug('Ho compilato lo script ' . $file, 'core');
                } else {
                    Logger::debug('Non sono riuscito a compilare ' . $file . ' ' . print_r($d, true), 'core');
                }
            }
        }
    }

    public function get($name, $type = null) {
        if (is_null($type)) {
            $type = 'data';
        }

        if (!$this::isCached($name, $type)) {
            return null;
        }
        $cache_key = sprintf('cache_%s_%s', $type, $name);
        if (is_array($name)) {
            array_walk($name, function(&$value, $item, $content) {
                $type = $content[0];
                $value = sprintf('cache_%s_%s', $type, $value);
            }, array($type));
            // se il conteggio delle chiavi fetch è uguale a quello delle chiavi richieste, allora restituisco true
            return apc_fetch($name);
        }
        return apc_fetch($cache_key);
    }

    public function isCached($name, $type = null) {
        if (is_null($type)) {
            $type = 'data';
        }

        $cache_key = sprintf('cache_%s_%s', $type, $name);
        if (is_array($name)) {
            // cambio tutte le key
            array_walk($name, function(&$value, $item, $content) {
                $type = $content[0];
                $value = sprintf('cache_%s_%s', $type, $value);
            }, array($type));
            // se il conteggio delle chiavi fetch è uguale a quello delle chiavi richieste, allora restituisco true
            if (count(apc_exists($name)) == count($name)) {
                return true;
            }
            // altrimenti falso
            return false;
        } else {
            if (apc_exists($cache_key)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @param type $type
     */
    public function set($name, $value, $type = null, $ttl = 0) {
        if (is_null($type)) {
            $type = 'data';
        }
        $cache_key = sprintf('cache_%s_%s', $type, $name);
        if ((is_array($name)) && (is_array($value))) {
            array_walk($name, function(&$value, $item, $content) {
                $type = $content[0];
                $value = sprintf('cache_%s_%s', $type, $value);
            }, array($type));
            $newarray = array_combine($name, $value);
            apc_store($newarray, null, $ttl);
        } else {
            apc_store($cache_key, $value, $ttl);
        }
    }

    public function update($name, $value, $type = null, $ttl = 0) {
        $this->set($name, $value, $type, $ttl);
    }

    public function clearScripts() {
        \apc_clear_cache('system');
    }

    public function clearCache() {
        \apc_clear_cache('user');
    }

    /**
     * 
     * @param String|Array $name
     * @param String $type
     */
    public function delete($name, $type = null) {
        if (is_null($type)) {
            $type = 'data';
        }
        if (is_array($name)) {
            array_walk($name, function(&$value, $item, $content) {
                $type = $content[0];
                $cache_key = sprintf('cache_%s_%s', $type, $value);
                apc_delete($cache_key);
            }, array($type));
        } else {
            $cache_key = sprintf('cache_%s_%s', $type, $name);
            apc_delete($cache_key);
        }
    }

    // get/set/update è per variabili
    // 
}
