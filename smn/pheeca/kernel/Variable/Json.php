<?php
namespace smn\pheeca\kernel\Variable;

use \smn\pheeca\kernel\Variable as Variable;

/**
 * Description of Xml
 *
 * @author Simone
 */

class Json extends Variable {
    // inserire metodi per il file
    // o per parsare un json da stringa
    
    protected $_file;
    
    public function file($filename) {
        if (is_readable($filename)) {
            $this->_file = $filename;
            parent::__construct(json_decode(file_get_contents($filename)));
        }
    }

    public function string($string) {
        $this->_file = null;
        parent::__construct(json_decode($string));
    }
}