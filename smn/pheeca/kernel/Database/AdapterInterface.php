<?php
namespace smn\pheeca\kernel\Database;

/**
 * @author Simone Esposito
 */
interface AdapterInterface {
    

    /**
     * @return \PDO|Resource
     */
    public function getDbInstance();

    /**
     * Imposta l'autocommit
     * @param type $set
     */
    public function autoCommit($set = true);

    /**
     * Esegue il commit
     */
    public function commit();

    /**
     * Da inizio ad una transizione
     * @return type
     * @throws Database_Exception
     */
    public function initTransition();

    /**
     * Restituisce true o false se la transizione esiste
     * @return Boolean
     */
    public function isTransition();

    /**
     * Esegue un rollback
     */
    public function rollback();

    /**
     * 
     * @param type $query
     * @param type $bindparams
     */
    public function query($query);
}
