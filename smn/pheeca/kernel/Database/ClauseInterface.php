<?php
namespace smn\pheeca\kernel\Database;

/**
 *
 * @author Simone Esposito
 */
interface ClauseInterface {
    
    public function toString();
    
    public function formatString();    
}
