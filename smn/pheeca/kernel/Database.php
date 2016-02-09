<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\Rowset;

class Database {

    /**
     * Contiene tutte le resource di connessione ai database
     * @var Database\AdapterInterface 
     */
    protected static $_connections;
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
            $username = $database['user'];
            $password = $database['pass'];
            $hostname = $database['host'];
            $port = $database['port'];
            $dbname = $database['database'];
            $otherOptions = $database['options'];
            $driver = $database['driver'];

            $class = self::getDriverClassNameByDriverName($driver);
            self::$_connections[$index] = new $class($hostname, $port, $dbname, $username, $password, $otherOptions);
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
     * Esegue la query $query con i parametri indicati in $bind_params sul driver $name
     * Viene creato una classe PDOStatement con la query $query. Se $query è una classe
     * di tipo Query, viene estratta la query in formato testo dalla classe $query
     * e in aggiunta i parametri da bindare, qualora ce ne siano, vengono prelevati
     * sempre dalla stessa classe $query.
     * Se invece vengono inviati $bind_params in formato array monodimensionale, indipendentemente dal fatto
     * che $query abbia dei parametri bindati, saranno presi in considerazione quelli
     * inviati al metodo.
     * @param String|\PDOStatement|Query $query Query da eseguire. Può essere una stringa, un PDOStatement o una classe Query
     * @param Array $bind_params Lista dei parametri da bindare nella query $query
     * @params String $connection_name Nome del connection name sul quale eseguire la query
     * @params Integer $fetch_style Constante PDO::FETCH_* da utilizzare per il metodo PDOStatement::fetchAll(). Di default
     * viene utilizzato PDO::FETCH_ASSOC 
     * @return Array
     */
    public static function query($query, $bind_params = null, $connection_name = 'default', $fetch_style = \PDO::FETCH_ASSOC) {
        if (!array_key_exists($connection_name, self::$_connections)) {
            return false;
        }
        $adapter_class = self::$_connections[$connection_name];
        return $adapter_class->query($query, $bind_params, $fetch_style);
    }
    
    public static function callProcedure($query, $bind_params = null, &$return_params = array(), $connection_name = 'default') {
        if (!array_key_exists($connection_name, self::$_connections)) {
            return false;
        }
        $adapter_class = self::$_connections[$connection_name];
        return $adapter_class->callProcedure($query, $bind_params, $return_params);
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
        $classname = $namespace .ucfirst($clause_name);
        if (!class_exists($classname)) {
            $classname = self::$_defaultClauseNS .ucfirst($clause_name);
        }
        return $classname;
    }

    /**
     * Restituisce una classe query e le assegna un driver
     * @param type $class_name
     * @param type $name
     */
    public static function getQueryClass($name = 'default') {
        if (self::getClauseNSFromName($name)) {
            // deve ri
        }
        return false;
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
