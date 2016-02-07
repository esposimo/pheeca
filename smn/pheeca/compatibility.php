<?php


if (version_compare(phpversion(), 5.4, '<')) {
    function trait_exists($traitname, $autoload = false) {
        return false;
    }
}

if (!function_exists('opcache_is_script_cached')) {
    function opcache_is_script_cached($file) {
        if (\extension_loaded('Zend OPCache')) {
            $opcached_files = opcache_get_status();
            if (array_key_exists('scripts', $opcached_files)) {
                foreach ($opcached_files['scripts'] as $cache_file) {
                    $fullpath = $cache_file['full_path'];
                    if (normalize_pathfile($fullpath) == normalize_pathfile($file)) {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

}