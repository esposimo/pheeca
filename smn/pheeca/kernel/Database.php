<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\Database\DatabaseException;
use \smn\pheeca\kernel\Validate\Exception as ValidateException;

class Database {

    /**
     * Contiene tutte le resource di connessione ai database
     * @var Array 
     */
    protected static $_connections;

    /**
     * Constante che indica il database di default sul quale eseguire le query
     */
    const DEFAULT_RESOURCE = 'default';

    protected static $_adapters = array(
        'mysql' => '\smn\pheeca\kernel\Database\Adapter\Mysql'
    );
    protected static $_clauses = array(
        'mysql' => '\smn\pheeca\kernel\Database\Clause\Mysql\\'
    );

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

            $class = self::getClassNameByAdaptersName($driver);
            self::$_connections[$index] = new $class($hostname, $port, $dbname, $username, $password, $otherOptions);
        }
    }

    public static function getClassNameByAdaptersName($name) {
        if (!array_key_exists($name, self::$_adapters)) {
            error_log('Non esiste il driver ' . $name);
            return;
        }
        $class = self::$_adapters[$name];
        return $class;
        //return new $class($adapter_name, $adapter_params);
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
     * @param type $name
     */
    public static function getClauseNSFromName($name = 'default') {
        if (!array_key_exists($name, self::$_clauses)) {
            error_log('Non esiste il driver ' . $name);
            return false;
        }
        $namespace = self::$_clauses[$name];
        return $namespace;
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