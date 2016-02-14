<?php
namespace smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\BindableClauseInterface;

/**
 * Description of Transaction
 *
 * @author Simone Esposito
 */
class Transaction implements \Iterator, BindableClauseInterface {

    protected $_queries = array();
    protected $_bind_params = array();
    protected $_transaction_string = null;
    protected $_position = 0;

    /**
     * Configura una transaction
     * @param Array $queries Array misto di query in formato Query 
     * @param String $connection_name
     */
    public function __construct($queries, $connection_name = 'default') {
        $this->_queries = $queries;
    }

    public function getStringTransaction() {
        return implode(';', $this->_queries);
    }

    public function current() {
        return $this->_queries[$this->_position];
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
        return isset($this->_queries[$this->_position]);
    }
    
    /**
     * 
     * @return String Restituisce la stringa da usare per lo statement
     */
    public function getQueryString() {
        $query = $this->current();
        if ($query instanceof Query) {
            return $query->toString();
        }
        if (is_array($query)) {
            return $query['query'];
        }
        return null;
    }
    
    /**
     * 
     * @return Array Restituisce l'array con i parametri da bindare, o un array vuoto se non ce ne sono.
     */
    public function getBindParams() {
        $query = $this->current();
        if ($query instanceof Query) {
            return $this->getBindParams();
        }
        if (is_array($query)) {
            return $query['params'];
        }
        return array();
    }
    

}
