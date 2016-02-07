<?php
namespace smn\pheeca\kernel\Database;


use \smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database\Clause\Mysql\Select;
use \smn\pheeca\kernel\Database\Clause\Mysql\From;
use \smn\pheeca\kernel\Database\Clause\Mysql\Where;

/**
 * Description of Query
 *
 * @author Simone Esposito
 */
class Query {
    //put your code here
    
    
    protected $_queryStatement = array();
    
    
    public function __construct($dataQuery, $adapter = 'default') {
        
        $clauseNamespace = Database::getClauseNamespace($adapter);
        foreach($dataQuery as $clause => $data) {
            $class = $clauseNamespace .$clause;
            $this->_queryStatement[] = new $class($data);
        }
    }
    
    
    public function select($fields, $prefix = '', $suffix = '') {
        $this->_select = new Select($fields, $prefix, $suffix);
    }
    
    public function from($tables, $prefix = '', $suffix = '') {
        $this->_from = new From($tables, $prefix, $suffix);
    }
    
    public function where($condition, $prefix = '', $suffix = '') {
        $this->_where = new Where($condition, $prefix, $suffix);
    }
    
    
    
    
    public function toString() {
        $finalQuery = array();
        foreach($this->_queryStatement as $query) {
            $finalQuery[] = $query->toString();
        }
        return implode(' ', $finalQuery);
    }
    
}
