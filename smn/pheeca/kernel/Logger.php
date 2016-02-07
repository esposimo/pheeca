<?php
namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\Logger\Writer;


class Logger {
    /*
     * Lista dei writers, che si occupano di scrivere nei file
     */

    const ALL = 'logOnAll';

    protected static $_writers = array();

    /**
     * Lista dei writers
     * @param type $options
     */
    public static function initialize($options = array()) {
        foreach ($options as $name => $logger) {
            $logger = (object) $logger;
            $writer = new Writer($logger->filename, $logger->level, array(), $logger->model);
            self::$_writers[$name] = $writer;
        }
    }

    /**
     * 
     * @param String $name
     * @param Array $arguments
     *  $arguments[0] => Stringa
     *  $arguments[1] => Livello
     */
    public static function __callStatic($name, $arguments) {
        $level = strtolower($name);
        $message = $arguments[0];
        $writerName = (isset($arguments[1])) ? $arguments[1] : null;
        
        
        if ($writerName == 'all') {
            foreach (self::$_writers as $writer) {
                $writer->log($message, $level);
            }
        }
       else if (array_key_exists($writerName, self::$_writers)) {
            $writer = self::$_writers[$writerName];
            $writer->log($message, $level);
        }
    }

}
