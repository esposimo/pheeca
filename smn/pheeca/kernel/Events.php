<?php
namespace smn\pheeca\kernel;

/**
 * Description of Events
 *
 * @author Simone Esposito
 */
class Events {
    
    
    protected static $_events = array();
    
    
    public static function addEvent($name, $callback, $return = false) {
        self::$_events[$name][] = array('callback' => $callback, 'return' => $return);
    }
    
    
    public static function trigger($name, $args = array()) {
        if (array_key_exists($name, self::$_events)) {
            $events = self::$_events[$name];
            foreach($events as $event) {
                $callback = $event['callback'];
                call_user_func_array($callback, $args);
            }
        }
    }
    
    
}
