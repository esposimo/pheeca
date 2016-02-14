<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database;
/**
 * Description of Model
 *
 * @author Simone Esposito
 */
class Model {

    protected $_connection_name;
    protected $_statements = array();

    public function __construct($connection_name = 'default') {
        $this->_connection_name = $connection_name;
    }

    /**
     * Aggiunge uno statement
     * @param String $name
     * @param Query|RunnableClauseInterface|Transaction $statement
     */
    public function addStatement($name, $statement) {
        $this->_statements[$name] = $statement;
    }

    /**
     * Restituisce uno statement
     * @param type $name
     * @return boolean
     */
    public function getStatementByName($name) {
        if (array_key_exists($name, $this->_statements)) {
            return $this->_statements[$name];
        }
        return false;
    }
    
    /**
     * Esegue uno statement
     * @param String $name
     * @return Rowset
     */
    public function run($name) {
        if ($this->getStatementByName($name)) {
            return Database::query($this->getStatementByName($name));
        }
    }
}
