<?php
namespace smn\pheeca\kernel\Database;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Gestisce il risultato di una query
 */
class Rowset implements \Iterator {

    protected $_position = 0;
    protected $_rowset = array();

    public function __construct($data = array()) {
        $this->_rowset = $data;
        $this->_position = 0;
    }

    public function current() {
        return $this->_rowset[$this->_position];
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        $this->_position++;
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function valid() {
        return isset($this->_rowset[$this->_position]);
    }
    
    public function first() {
        return $this->_rowset[0];
    }
    
    public function last() {
        return $this->_rowset[(count($this->_rowset)-1)];
    }

    public function __call($name, $arguments) {
        $rownumber = $arguments[0];
        if ((isset($this->_rowset[$rownumber])) && (isset($this->_rowset[$rownumber]->$name))) {
            return $this->_rowset[$rownumber]->$name;
        }
        return false;
    }

}