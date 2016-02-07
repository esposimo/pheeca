<?php

namespace smn\pheeca\kernel;

/**
 * Description of Loader
 *
 * @author Simone Esposito
 */
class Loader {

    //put your code here


    protected static $_classPath = array();
    protected static $_extensions = array('.php', '.class.php');
    protected static $_pathSeparator = PATH_SEPARATOR;

    public static function addClassPath($path) {
        self::$_classPath[] = $path;
    }

    public static function loadClass($class) {
        foreach (self::$_classPath as $path) {
            foreach (self::$_extensions as $ext) {
                $filename = $path . '/' . preg_replace('/\x5c/', '/', $class) . $ext;
                if (is_readable($filename)) {
                    require_once $filename;
                    return;
                }
            }
        }
    }
}
