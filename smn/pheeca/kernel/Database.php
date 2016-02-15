<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\Rowset;
use \smn\pheeca\kernel\Database\Transaction;

class Database {

    /**
     * Contiene tutte le resource di connessione ai database
     * @var Database\AdapterInterface 
     */
    protected static $_connections;

    /**
     *
     * @var Array \PDO
     */
    protected static $_driver_used = array();

    /**
     * Constante che indica il database di default sul quale eseguire le query
     */
    const DEFAULT_RESOURCE = 'default';

    protected static $_drivers = array(
        'mysql' => '\smn\pheeca\kernel\Database\Adapter\Mysql'
    );
    protected static $_clauses = array(
        'mysql' => '\smn\pheeca\kernel\Database\Clause\Mysql\\'
    );
    protected static $_defaultClauseNS = '\smn\pheeca\kernel\Database\Clause\\';

    /**
     * 
     * @param Array $database_connections
     */
    public static function initialize($database_connections) {
        foreach ($database_connections as $index => $database) {
            if (!array_key_exists('driver', $database)) {
                trigger_error('Nessun driver indicato!');
                return;
            }
            // check config, se ne manca una , errore
            $dsn = $database['dsn'];
            $username = $database['user'];
            $password = $database['pass'];
            $options = $database['options'];
            $driver = $database['driver'];

            $class = self::getDriverClassNameByDriverName($driver);
            self::$_connections[$index] = new $class($dsn, $username, $password, $options);
            self::$_driver_used[$index] = $driver;
        }
    }

    /**
     * Restituisce il nome della classe driver utilizzabile per la connessione 
     * in base al nome del driver indicato
     * @param String $name
     * @return String
     */
    public static function getDriverClassNameByDriverName($name) {
        if (!array_key_exists($name, self::$_drivers)) {
            error_log('Non esiste il driver ' . $name);
            return;
        }
        $class = self::$_drivers[$name];
        return $class;
        //return new $class($adapter_name, $adapter_params);
    }

    /**
     * Restituisce il driver utilizzato per la connessione indicata
     * @param String $connection_name
     * @return String|Null
     */
    public static function getDriverNameByConnectionName($connection_name = 'default') {
        if (array_key_exists($connection_name, self::$_driver_used)) {
            $driver = self::$_driver_used[$connection_name];
            return $driver;
        }
        return null;
    }

    /**
     * @deprecated since version number
     * @param type $query
     * @param type $name
     * @return type
     */
    public static function getStatement($query, $name = 'default') {
        $pdo = self::getPDOLinkFromConnectionName($name);
        $stmt = $pdo->prepare($query);
        return $stmt;
    }

    /**
     * Richiama il metodo query dell'adapter associato al connection name $connection_name
     * @param String|\PDOStatement|Query $query Query da eseguire. Può essere una stringa, un PDOStatement o una classe Query
     * @param Array $bind_params Lista dei parametri da bindare nella query $query
     * @params String $connection_name Nome del connection name associato all'adapter che eseguirà la query
     * @params Integer $fetch_style Constante PDO::FETCH_* da utilizzare per il metodo PDOStatement::fetchAll(). Di default
     * viene utilizzato PDO::FETCH_ASSOC 
     * @return Array
     */
    public static function query($query, $bind_params = null, $connection_name = 'default', $fetch_style = \PDO::FETCH_OBJ) {
        if (!array_key_exists($connection_name, self::$_connections)) {
            return false;
        }
        $adapter_class = self::$_connections[$connection_name];
        return $adapter_class->execquery($query, $bind_params, $fetch_style);
    }

    /**
     * Richiama il metodo callProcedure dell'adapter associato al connection name $connection_name
     * @param String|\PDOStatement|Query $query Query da eseguire
     * @param Array $bind_params Lista dei parametri da bindare nel caso in cui $query sia una stringa. Vanno bindati sia i parametri di input che di output
     * @param Array $return_params Lista dei valori che saranno restituiti dalla procedura
     * @param String $connection_name
     * @return boolean
     */
    public static function callProcedure($query, $bind_params = null, &$return_params = array(), $connection_name = 'default') {
        if (!array_key_exists($connection_name, self::$_connections)) {
            return false;
        }
        $adapter_class = self::$_connections[$connection_name];
        return $adapter_class->callProcedure($query, $bind_params, $return_params);
    }

    public static function transaction(Transaction $transaction, $auto_commit = true, $connection_name = 'default') {
        if (!array_key_exists($connection_name, self::$_connections)) {
            return false;
        }
        $adapter_class = self::$_connections[$connection_name];
        $adapter_class->transaction($transaction, $auto_commit);
    }

    /**
     * Restituisce la PDO 
     * @param type $name
     * @return \PDO
     */
    public static function getPDOLinkFromConnectionName($name = 'default') {
        $class = self::$_connections[$name];
        return $class->getDbInstance();
    }

    /**
     * Restituisce il namespace per le clausole in base al driver indicato
     * @param String $driver
     * @return String 
     */
    public static function getClauseNSFromDriverName($driver = null) {
        if ((is_null($driver)) || (!array_key_exists($driver, self::$_clauses))) {
            $namespace = self::$_defaultClauseNS;
        } else {
            $namespace = self::$_clauses[$driver];
        }
        return $namespace;
    }

    /**
     * Restituisce il namespace per le clausole in base al nome connessione indicato
     * Il metodo ricava il driver del nome connessione indicato e poi ricava il namespace
     * con il metodo getClauseNSFromDriverName. Se il namespace non esiste , restituisce
     * il namespace base delle clausole
     * @param String $connection_name
     * @return String|Null
     */
    public static function getClauseNSFromConnectionName($connection_name = 'default') {
        $driver = self::getDriverNameByConnectionName($connection_name);
        return self::getClauseNSFromDriverName($driver);
    }

    /**
     * Restituisce una classe Clause in base al nome ed al driver indicato
     * @param String $clause_name
     * @param String $connection_name
     * @return String
     */
    public static function getClauseClassNameFromConnectionName($clause_name, $connection_name = 'default') {
        $namespace = self::getClauseNSFromConnectionName($connection_name);
        $classname = $namespace . ucfirst($clause_name);
        if (!class_exists($classname)) {
            $classname = self::$_defaultClauseNS . ucfirst($clause_name);
        }
        return $classname;
    }

    /**
     * Restituisce in formato stringa la classe clause disponibile in base al driver.
     * Se viene indicata come $clause_name Select, e come $driver_name 'mysql', 
     * se esiste una Select Clause nel namespace Clause\Mysql , essa sarà restituita,
     * altrimenti sarà restituita la classe Clause\Select()
     * @param String $clause_name Nome della clausola
     * @param String $driver_name Nome del driver
     * @return String Nome della classe incluso il namespace
     */
    public static function getClauseClassNameFromDriverName($clause_name, $driver_name) {
        $namespace = self::getClauseNSFromDriverName($driver_name);
        $classname = $namespace . ucfirst($clause_name);
        if (!class_exists($classname)) {
            $classname = self::$_defaultClauseNS . ucfirst($clause_name);
        }
        return $classname;
    }
    
    /**
     * Restituisce una nuova istanza clause in base al nome richiesto e al driver indicato
     * @param String $clause_name
     * @param String $driver_name
     * @return Database\Clause
     */
    public static function getClauseClassInstanceFromDriverName($clause_name, $driver_name) {
        $classname = self::getClauseClassNameFromDriverName($clause_name, $driver_name);
        return new $classname();
    }
    
    /**
     * Restituisce una nuova istanza clause in base al nome richiesto e all'identificativo di connessione
     * @param String $clause_name
     * @param String $connection_name
     * @return Database\Clause
     */
    public static function getClauseClassInstanceFromConnectionName($clause_name, $connection_name = 'default') {
        $classname = self::getClauseClassNameFromConnectionName($clause_name, $connection_name);
        return new $classname();
    }

    /**
     * Restituisce una classe query e le assegna un driver
     * @param Array $clause_list Lista di clausole
     * @param String $name Nome della connessione
     * @return Query
     */
    public static function getQueryClass($clause_list = array(), $name = 'default') {
        return new Query($clause_list, $name);
    }

    /**
     * Aggiunge un nuovo driver $driver_name associando la classe $driver_class
     * @param String $driver_name Nome del driver
     * @param String $adapter_class Classe del driver inclusa di namespace
     */
    public static function addAdapterClass($driver_name, $driver_class) {
        self::$_drivers[$driver_name] = $driver_class;
    }

    /**
     * Aggiunge un nuovo namespace $clause_namespace per le classi Clause associandolo al driver $adapter_name
     * @param String $driver_name Nome del driver
     * @param String $clause_namespace Namespace di base per le classi clausole
     */
    public static function addClauseNamespace($driver_name, $clause_namespace) {
        self::$_clauses[$driver_name] = $clause_namespace;
    }

}
