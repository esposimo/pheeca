<?php

namespace smn\pheeca\kernel;

class Database {

    /**
     * Contiene tutte le resource di connessione ai database
     * @var Array 
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
     * @param String|\PDOStatement $query
     * @param Array $bind_params
     * @return Array
     */
    public static function query($query, $bind_params = array(), $name = 'default') {
        $pdo = self::getPDOLinkFromConnectionName($name);
        $stmt = $pdo->prepare($query);
        $stmt->execute($bind_params);
        return $stmt->fetchAll();
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
     * Aggiunge un nuovo driver $adapter_name associando la classe $adapter_alias
     * @param String $adapter_name Nome del driver
     * @param String $adapter_class Classe del driver inclusa di namespace
     */
    public static function addAdapterClass($adapter_name, $adapter_class) {
        self::$_adapters[$adapter_name] = $adapter_class;
    }

    /**
     * Aggiunge un nuovo namespace $clause_namespace per le classi Clause associandolo al driver $adapter_name
     * @param String $adapter_name Nome del driver
     * @param String $clause_namespace Namespace di base per le classi clausole
     */
    public static function addClauseNamespace($adapter_name, $clause_namespace) {
        self::$_clauses[$adapter_name] = $clause_namespace;
    }

}
