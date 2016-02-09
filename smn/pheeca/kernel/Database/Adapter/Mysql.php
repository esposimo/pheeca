<?php

namespace smn\pheeca\kernel\Database\Adapter;

use \smn\pheeca\kernel\Database\AdapterInterface;
use \smn\pheeca\kernel\Database\DatabaseException;
use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\Rowset;

/**
 * @author Simone Esposito
 */
class Mysql implements AdapterInterface {

    /**
     * 
     * @var Resource 
     */
    protected $_dbInstance;

    /**
     * Inizializza la connessione
     * @param type $hostname
     * @param type $port
     * @param type $database
     * @param type $username
     * @param type $password
     * @param type $options
     */
    public function __construct($hostname = 'localhost', $port = '3306', $database = '', $username = '', $password = '', $options = array()) {
        $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s', $database, $hostname, $port);
        $this->_dbInstance = new \PDO($dsn, $username, $password, $options);
        $this->_dbInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Restituisce l'istanza della connessione
     * @return \PDO
     */
    public function getDbInstance() {
        return $this->_dbInstance;
    }

    /**
     * Imposta l'autocommit
     * @param type $set
     */
    public function autoCommit($set = true) {
        $this->_dbInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, $set);
    }

    /**
     * Esegue il commit
     */
    public function commit() {
        $this->_dbInstance->commit();
    }

    /**
     * Da inizio ad una transizione
     * @return type
     * @throws Database_Exception
     */
    public function initTransition() {
        $init = $this->_dbInstance->beginTransaction();
        if (!$this->_dbInstance->beginTransaction()) {
            throw new Database_Exception('No transaction init');
        }
        return $init;
    }

    /**
     * Restituisce true o false se la transizione esiste
     * @return Boolean
     */
    public function isTransition() {
        return $this->_dbInstance->inTransaction();
    }

    /**
     * Esegue un rollback
     */
    public function rollback() {
        $this->_dbInstance->rollBack();
    }

    public function query($query, $bind_params = null, $fetch_style = \PDO::FETCH_ASSOC) {
        $pdo = $this->getDbInstance();
        $string = $query;
        $params = array();
        if ($query instanceof Query) {
            $string = $query->toString();
            $params = $query->getBindParams();
            $stmt = $pdo->prepare($string);
        } else if ($query instanceof \PDOStatement) {
            $stmt = $query;
        } else {
            $stmt = $pdo->prepare($query);
        }

        if (!is_null($bind_params)) {
            $params = $bind_params;
        }

        try {
            $stmt->execute($params);
            $result = $stmt->fetchAll($fetch_style);
            return new Rowset($result);
        } catch (\PDOException $ex) {
            echo $ex->getMessage();
            return false;
        }
    }
    /**
     * Esegue una procedura ed inserisce eventuali parametri di ritorno in $return_params
     * @param type $query
     * @param type $bind_params
     * @param type $return_params
     * @return boolean
     */
    public function callProcedure($query, $bind_params = null, &$return_params = null) {
        $pdo = $this->getDbInstance();
        $string = $query;
        $params = array();
        if ($query instanceof Query) {
            $string = $query->toString();
            $params = $query->getBindParams();
            $stmt = $pdo->prepare($string);
        }
        else if ($query instanceof \PDOStatement) {
            $stmt = $query;
        }
        else {
            $stmt = $pdo->prepare($query);
        }
        
        if (!is_null($bind_params)) {
            $params = $bind_params;
        }
        
        try {
            $stmt->execute($params);
            if (is_null($return_params)) {
                return true; // procedure eseguita
            }
            // altrimenti se non è null vuol dire che è stato passato e quindi devo ricavarmi i valori
            // in mysql non è possibile usare la costante \PDO::PARAM_INPUT_OUTPUT in quanto genera errore
            // quindi faccio una select sulle variabili indicate in fase di chiamata
            $selectclassname = \smn\pheeca\kernel\Database::getClauseClassNameFromDriverName('select', 'mysql');
            $select_class = new $selectclassname($return_params);
            $stmt = $pdo->prepare($select_class->toString());
            $stmt->execute($return_params);
            $return_params = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $return_params;
        } catch (\PDOException $ex) {
            echo 'Adapter in exception : ' .$ex->getMessage();
            return false;
        }
        
    }
}
