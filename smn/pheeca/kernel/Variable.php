<?php
namespace smn\pheeca\kernel;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Variable
 *
 * @author Simone
 */
class Variable {
    
    /**
     * Dati storicizzati
     * @var Mixed 
     */
    protected $_data = array();
    
    
    /**
     * 
     * @param Array $data
     */
    public function __construct($data = array()) {
        foreach($data as $key => $value) {
            if (is_array($value)) {
                $this->_data[$key] = new static($value);
            }
            else if (gettype($value) == 'object') {
                if (get_class($value) == 'stdClass') {
                    // for json parse
                    $this->_data[$key] = new static(get_object_vars($value));
                }
            }
            else {
                $this->_data[$key] = $value;
            }
        }
//        echo '<pre>';
//        print_r($this);
//        echo '</pre>';
    }
    
    public function __get($name) {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return false;
    }
    
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }
    
    
}
