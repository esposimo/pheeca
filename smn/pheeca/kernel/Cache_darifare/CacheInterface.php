<?php

namespace smn\pheeca\kernel\Cache;

/**
 *
 * @author Simone
 */
interface CacheInterface {

    public function compileScripts();

    public function clearScripts();

    public function __construct($options = array());

    public function get($name, $type = null);

    public function set($name, $value, $type = null, $ttl = 0);

    public function isCached($name, $type = null);

    public function update($name, $value, $type = null, $ttl = 0);

    public function delete($name, $type = null);

    public function clearCache();
}
