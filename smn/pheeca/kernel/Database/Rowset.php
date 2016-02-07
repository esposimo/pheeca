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

    public function __get($name) {
        $upper = strtoupper($name);
        $rowset = $this->_rowset[$this->_position];
        if (array_key_exists($upper, array_change_key_case($rowset, CASE_UPPER))) {
            return $this->_rowset[$this->_position][$upper];
        }
    }

    public function __call($name, $arguments) {
        $upper = strtoupper($name);
        $num = $arguments[0];
        if ($num > count($this->_rowset)) {
            return null;
        }
        $data = $this->_rowset[$num];
        if (array_key_exists($upper, array_change_key_case($data, CASE_UPPER))) {
            return $this->_rowset[$num][$upper];
        }
        return null;
    }

}