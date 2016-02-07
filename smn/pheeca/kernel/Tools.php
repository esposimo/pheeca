<?php
namespace smn\pheeca\kernel;

/**
 * Description of Tools
 *
 * @author Simone Esposito
 */
class Tools {
    
    
    public static function getClassInstanceByName($classname, $namespace = '\\', $parameters = array()) {
        $name = $namespace .$classname;
        $reflection = new \ReflectionClass($name);
        return $reflection->newInstanceArgs($parameters);
    }
}
