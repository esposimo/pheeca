<?php

namespace smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database;
use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\BindableClauseInterface;
use \smn\pheeca\kernel\Database\QueryStatementInterface;

/**
 * Description of Query
 *
 * @author Simone Esposito
 */
class Query implements QueryStatementInterface {

    protected $_connectionName;

    /**
     *
     * @var type 
     */
    protected $_clauseList = array();

    /**
     * 
     * @param Array $clauselist Lista delle clausole
     * @param String $connection_name Nome della connessione
     */
    public function __construct($clauselist = array(), $connection_name = 'default') {
        $this->setConnectionName($connection_name);
        $this->processClauseList($clauselist);
    }

    /**
     * Lista di clausole da aggiungere
     * @param Array $clauselist
     */
    public function processClauseList($clauselist = array()) {
        foreach ($clauselist as $clausename => $clausedata) {
            if (($clausedata instanceof Query) || ($clausedata instanceof Clause)) {
                $this->_clauseList[$clausename] = $clausedata;
            } else {
                if (is_numeric($clausename)) {
                    $instance = Database::getClauseClassInstanceFromConnectionName($clausedata, $this->getConnectionName());
                    $this->_clauseList[$clausedata] = $instance;
                } else {
                    $instance = Database::getClauseClassInstanceFromConnectionName($clausename, $this->getConnectionName());
                    $instance->initData($clausedata);
                    $this->_clauseList[$clausename] = $instance;
                }
            }
        }
    }

    /**
     * Aggiunge una clause alla query. Se $clause è una clause viene aggiunta così com'è,
     * se è una stringa viene richiamata la Clause più vicina al driver indicato per la query
     * e passa $params al costruttore
     * @param String|Clause $clause
     * @param Array $params
     * @return self
     */
    public function addClause($clause, $params = array()) {
        if ($clause instanceof Clause) {
            $name = $clause->getName();
            $this->_clauseList[$name] = $clause;
        } else {
            $instance = Database::getClauseClassInstanceFromConnectionName($clause, $this->getConnectionName());
            $instance->initData($params);
            $this->_clauseList[$clause] = $instance;
        }
        return $this;
    }

    /**
     * Invia $params al costruttore (metodo initData()) della classe clause 
     * indicata in $clausename
     * @param String $clausename Nome della clausola
     * @param Mixed $params Parametri da inviare (solo data, senza suffix e prefix)
     * @return \smn\pheeca\kernel\Database\Query
     */
    public function sendData($clausename, $params = array()) {
        if (array_key_exists($clausename, $this->_clauseList)) {
            $instance = $this->_clauseList[$clausename];
            $instance->setData($params);
        }
        return $this;
    }

    /**
     * Invia il suffisso $suffix alla clause $clausename
     * @param String $clausename
     * @param Mixed $params
     * @return self
     */
    public function sendSuffix($clausename, $suffix = '') {
        if (array_key_exists($clausename, $this->_clauseList)) {
            $instance = $this->_clauseList[$clausename];
            $instance->setSuffix($suffix);
        }
        return $this;
    }

    /**
     * Invia il prefisso $prefix alla clause $clausename
     * @param String $clausename
     * @param Mixed $prefix
     * @return self
     */
    public function sendPrefix($clausename, $prefix = '') {
        if (array_key_exists($clausename, $this->_clauseList)) {
            $instance = $this->_clauseList[$clausename];
            $instance->setPrefix($prefix);
        }
        return $this;
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
            $query .= $clause->toString() . ' ';
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
    public function exec() {
        return Database::query($this, null, $this->getConnectionName());
    }

}
