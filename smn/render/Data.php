<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of render_Data
 *
 * @author Simone
 */

class render_Data implements Iterator {

    protected $_data = array();
    private $position = 0;

    public function __construct($arrayOfData = null) {

        $this->position = 0;
        if (is_array($arrayOfData)) {
            $this->_data = $arrayOfData;
        }
    }

    public function __get($name) {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return false;
    }

    public function __set($name, $value = '') {
        $this->_data[$name] = $value;
    }
    
    public function __isset($name) {
        if (array_key_exists($name, $this->_data)) {
            return true;
        }
        return false;
    }
    private function getNameFromPosition() {
        $keys = array_keys($this->_data);
        $position = $this->position;
        
        if ($this->position >= count($keys)) {
            return null;
        }
        return $keys[$position];
    }

    public function current() {
        return $this->_data[$this->getNameFromPosition()];
    }

    public function key() {
        return $this->getNameFromPosition();
    }

    public function next() {
        ++$this->position;
    }

    public function rewind() {
        $this->position = 0;
        
    }

    public function valid() {
        return isset($this->_data[$this->getNameFromPosition()]);
    }

}
