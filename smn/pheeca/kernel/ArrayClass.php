<?php

namespace smn\pheeca\kernel;

/**
 * Description of ArrayClass
 *
 * @author Simone Esposito
 */
class ArrayClass {

    protected $_array = array();
    protected $_callback = false;
    protected $_restore = array();

    public function __construct($array = array(), $callback = false) {
        $this->_array = $array;
        $this->_restore = $array;
        $this->_callback = $callback;
    }

    public function setArray($array) {
        $this->_array = $array;
    }

    public function getArray() {
        return $this->_array;
    }

    /**
     * Restituisce l'elemento o gli elementi richiesti in $keys
     * Se $keys è un array, restituisce solo gli elementi dell'array di base
     * che hanno come indice gli elementi di $keys
     * 
     * @param String|Array $keys Una chiave dell'array o un'array di valori
     * contenenti le chiavi
     */
    public function getKey($keys) {
        if (is_string($keys) || (is_numeric($keys))) {
            if (array_key_exists($keys, $this->_array)) {
                return $this->_array[$keys];
            }
        }
        if (is_array($keys)) {
            $this->_array = array_intersect_key($this->_array, array_flip($keys));
            return $this->_array;
        }
        return false;
    }

    /**
     * Elimina una o più chiavi dall'array
     * @param String|Array $keys Chiave o array di chiave da eliminare 
     * dall'array
     * base
     * @return boolean
     */
    public function delKey($keys) {
        if (is_string($keys) || (is_numeric($keys))) {
            if (array_key_exists($keys, $this->_array)) {
                unset($this->_array[$keys]);
                return true;
            }
        }
        if (is_array($keys)) {
            $false = false;
            $data = array(&$false, &$this->_array);
            array_walk($keys, function($value, $key, &$data) {
                if (array_key_exists($value, $data[1])) {
                    unset($data[1][$value]);
                    $data[0] = true;
                }
            }, $data);
            return $data[0];
        }
        return false;
    }

    public function delValue($value) {
        if (is_string($value) || (is_numeric($value))) {
            if (($key = array_search($value, $this->_array)) !== false) {
                unset($this->_array[$key]);
                return true;
            }
            return false;
        }
        if (is_array($value)) {
            $false = false;
            $data = array(&$false, &$this->_array);
            array_walk($value, function($v, $k, &$data) {
                if (($key = array_search($v, $data[1])) !== false) {
                    unset($data[1][$key]);
                    $data[0] = true;
                }
            }, $data);
            return $data[0];
        }
        return false;
    }

    public function runCallback($other_params = array()) {
        array_walk($this->_array, $this->_callback, $other_params);
    }
    
    
    public function restoreData() {
        $this->_array = $this->_restore;
    }
    
    
    public static function staticGetKey($array, $keys, $callback = false, $other_params = array()) {
        $self = new self($array, $callback);
        $self->getKey($keys);
        if ($callback !== false) {
            $self->runCallback($other_params);
        }
        return $self->getArray();
    }
    
    public static function staticDelKey($array, $keys, $callback = false, $other_params = array()) {
        $self = new self($array, $callback);
        $self->delKey($keys);
        if ($callback !== false) {
            $self->runCallback($other_params);
        }
        return $self->getArray();
    }
    
    public static function staticDelValue($array, $values, $callback = false, $other_params = array()) {
        $self = new self($array, $callback);
        $self->delValue($keys);
        if ($callback !== false) {
            $self->runCallback($other_params);
        }
        return $self->getArray();
    }
}
