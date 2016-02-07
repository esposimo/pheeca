<?php
namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\MVC\ViewInterface;
/**
 * Description of Template
 *
 * @author Simone Esposito
 */
class Template implements ViewInterface {
    
    
    protected $_templateClass = null;
    
    public function __construct(ViewInterface $templateClass) {
        $this->_templateClass = $templateClass;
    }

    public function getContent() {
        return $this->_templateClass->getContent();
    }

    public function outputContent() {
        $this->_templateClass->outputContent();
    }

}
