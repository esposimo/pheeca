<?php
namespace smn\pheeca\kernel\Database;

/**
 *
 * @author Simone Esposito
 */
interface RunnableClauseInterface {
    public function getQueryString();
    
    public function getBindParams();
}
