<?php
namespace smn\pheeca\kernel\Database;


use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\BindableClauseInterface;


/**
 * Description of Query
 *
 * @author Simone Esposito
 */
class Query implements QueryStatement {

    protected $_connectionName;

    /**
     *
     * @var type 
     */
    protected $_clauseList = array();

    public function __construct($clauselist = array(), $connection_name = 'default') {
        $this->setConnectionName($connection_name);
        foreach ($clauselist as $clausename => $clausedata) {
            if (($clausedata instanceof Query) || ($clausedata instanceof Clause)) {
                $this->_clauseList[$clausename] = $clausedata;
            } else {
                $class = \smn\pheeca\kernel\Database::getClauseClassNameFromConnectionName($clausename, $this->getConnectionName());
                $reflection = new \ReflectionClass($class);
                $instance = $reflection->newInstanceArgs($clausedata);
                $this->_clauseList[$clausename] = $instance;
            }
        }
    }

    public function __toString() {
        return $this->getStringQuery();
    }

    public function toString() {
        return $this->getStringQuery();
    }

    public function setConnectionName($name) {
        $this->_connectionName = $name;
    }

    public function getConnectionName() {
        return $this->_connectionName;
    }

    /**
     * Restituisce la query in formato stringa
     * @return String
     */
    public function getStringQuery() {
        $query = '';
        foreach ($this->_clauseList as $clause) {
            $query .= $clause->toString();
        }
        return $query;
    }

    public function getBindParams() {
        $params = array();
        foreach ($this->_clauseList as $clause) {
            if (($clause instanceof BindableClauseInterface) || ($clause instanceof Query)) {
                $params = array_merge($params, $clause->getBindParams());
            }
        }
        return $params;
    }

    /**
     * Esegue la query
     */
    public function execQuery() {
        echo '<pre>';
        print_r($this->getBindParams());
        echo '</pre>';
    }

}
