<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database;
use \smn\pheeca\kernel\Database\Model;

/**
 * Description of Table
 *
 * @author Simone Esposito
 */
class Table extends Model {

    const QUERY_GET_TABLE = 'QUERY_GET_TABLE';

    protected $_table = '';
    
    protected $_pkeys = array();

    public function __construct($table = '', $connection_name = 'default') {
        if ($table != '') {
            $this->_table = $table;
        }
        parent::__construct($connection_name);
        $query = Database::getQueryClass(array('select', 'from'), $this->_connection_name);
        $query->sendData('from', array($this->getTable()));
        $this->addStatement(
                self::QUERY_GET_TABLE, 
                $query
        );
    }

    public function setTable($name) {
        $this->_table = $name;
    }

    public function getTable() {
        return $this->_table;
    }

    public function getAllTable() {
        return $this->run(self::QUERY_GET_TABLE);
    }
    
    
    public function getByPrimaryKey($pkeys = array()) {
        if (is_array($pkeys)) {
            reset($this->_pkeys);
            $condition = array();
            foreach($pkeys as $value) {
                $column = current($this->_pkeys);
                next($this->_pkeys);
                $condition[] = array('column' => $column, 'value' => $value);
            }
            reset($this->_pkeys);
            $where = Database::getClauseClassInstanceFromConnectionName('where', $this->_connection_name);
            $where->setData($condition);
        }
        else {
            reset($this->_pkeys);
            $value = $pkeys;
            $column = current($this->_pkeys);
            $condition = array(array('column' => $column, 'value' => $value));
            $where = Database::getClauseClassInstanceFromConnectionName('where', $this->_connection_name);
            $where->setData($condition);
        }
        $query = Database::getQueryClass(array('select','from','where' => $where),$this->_connection_name);
        $query->sendData('from', array($this->getTable()));
        return Database::query($query);
    }
    

    public static function staticGetAllTable($table_name, $connection_name = 'default') {
        $self = new self($table_name, $connection_name);
        return $self->getAllTable();
    }

}
