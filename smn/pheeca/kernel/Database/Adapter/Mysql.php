<?php

namespace smn\pheeca\kernel\Database\Adapter;

use \smn\pheeca\kernel\Database\AdapterInterface;
use \smn\pheeca\kernel\Database\DatabaseException;

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

    public function query($query) {
        // qui mi prendo l'istanza e se $query è un'interfaccia
        // 
        // qui prendo la $query e valuto
        // se è una stringa , la eseguo così com'è
        // se è un oggetto di tipo Select
        // valido i dati richiamando il metodo valid della select
        // se ci sono exception, le catturo
        // altrimenti eseguo la query inviando anche i parametri, che mi darà ovviamente la classe che rappresenta la query
        //
        //
        
        if ($query instanceof \smn\pheeca\kernel\Database\Statement\PDO\Select) {
            $query->validate();
            $pdo = $this->getDbInstance();
            $statement = $pdo->prepare($query->toString());
            $query->bindAllParams($statement);
            $statement->execute();
            if ($statement->errorCode() == 0) {
                $rowset = new \smn\pheeca\kernel\Database\Rowset($statement->fetchAll(\PDO::FETCH_ASSOC));
                return $rowset;
            } else {
                $code = $statement->errorInfo()[1];
                throw new DatabaseException(implode('|', $statement->errorInfo()), $code);
            }
        }

        if (is_string($query)) {
            $pdo = $this->getDbInstance();
            $statement = $pdo->prepare($query);
            $statement->execute();
            if ($statement->errorCode() == 0) {
                $rowset = new \smn\pheeca\kernel\Database\Rowset($statement->fetchAll(\PDO::FETCH_ASSOC));
                return $rowset;
            } else {
                $code = $statement->errorInfo()[1];
                throw new DatabaseException(implode('|', $statement->errorInfo()), $code);
            }
        }
    }

    public function test() {
        
    }

}
