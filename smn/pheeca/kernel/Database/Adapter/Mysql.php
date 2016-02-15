<?php

namespace smn\pheeca\kernel\Database\Adapter;

use \smn\pheeca\kernel\Database\DatabaseException;
use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\Rowset;
use \smn\pheeca\kernel\Database\Transaction;
use \smn\pheeca\kernel\Database\RunnableClauseInterface;

/**
 * @author Simone Esposito
 */
class Mysql extends \PDO {

    /**
     * 
     * @var Resource 
     */
    protected $_dbInstance;
    protected $_transaction_counter = 0;

    /**
     * Inizializza la connessione
     * @param type $dsn
     * @param type $username
     * @param type $password
     * @param type $options
     */
    public function __construct($dsn, $username, $password, $options = array()) {
        parent::__construct($dsn, $username, $password, $options);
//        $this->_dbInstance = new \PDO($dsn, $username, $password, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Restituisce l'istanza della connessione
     * @return \PDO
     */
    public function getDbInstance() {
        return $this;
    }

    /**
     * Imposta l'autocommit
     * @param Boolean $set
     */
    public function autoCommit($set = true) {
        $this->setAttribute(\PDO::ATTR_AUTOCOMMIT, $set);
    }

    /**
     * Da inizio ad una transizione
     * @return type
     * @throws Database_Exception
     */
    public function beginTransaction() {
        if ($this->_transaction_counter == 0) {
            parent::beginTransaction();
        } else {
            $savepoint = sprintf('SAVEPOINT LEVEL%s', $this->_transaction_counter);
            $this->exec($savepoint);
        }
        $this->_transaction_counter++;
    }

    /**
     * Esegue il commit
     */
    public function commit() {
        $this->_transaction_counter--;
        if ($this->_transaction_counter == 0) {
            parent::commit();
        } else {
            $releasepoint = sprintf('RELEASE SAVEPOINT LEVEL%s', $this->_transaction_counter);
            $this->exec($releasepoint);
        }
    }

    /**
     * Esegue un rollback
     */
    public function rollBack() {
        $this->_transaction_counter--;
        if ($this->_transaction_counter == 0) {
            parent::rollBack();
        } else {
            $savepoint = sprintf('ROLLBACK TO SAVEPOINT LEVEL%s', $this->_transaction_counter);
            $this->exec($savepoint);
        }
    }

    /**
     * Restituisce true o false se la transizione esiste
     * @return Boolean
     */
    public function isTransition() {
        return $this->inTransaction();
    }

    public function execquery($query, $bind_params = null, $fetch_style = \PDO::FETCH_OBJ) {
        $string = $query;
        $params = array();
        if (($query instanceof Query) || ($query instanceof RunnableClauseInterface)) {
            $string = $query->toString();
            $params = $query->getBindParams();
            $stmt = $this->prepare($string);
        } else if ($query instanceof \PDOStatement) {
            $stmt = $query;
        } else {
            $stmt = $this->prepare($query);
        }

        if (!is_null($bind_params)) {
            $params = $bind_params;
        }
        try {
            $stmt->execute($params);
            $result = $stmt->fetchAll($fetch_style);
            return new Rowset($result);
        } catch (\PDOException $ex) {
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
            echo 'Adapter in exception : ' . $ex->getMessage();
            return false;
        }
    }

    /**
     * 
     * @param Transaction $transaction
     */
    public function transaction(Transaction $transaction, $auto_commit = true) {
        try {
            $this->beginTransaction();
            foreach ($transaction as $query) {
                if ($query instanceof Query) {
                    $queryString = $query->toString();
                    $params = $query->getBindParams();
                }
                if (is_array($query)) {
                    $queryString = $query['query'];
                    $params = $query['params'];
                }
                $stmt = $this->prepare($queryString);
                $stmt->execute($params);
            }
            if ($auto_commit === true) {
                $this->commit();
            }
        } catch (\PDOException $exception) {
            echo $exception->getMessage();
        }
    }

}
