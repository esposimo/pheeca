<?php

namespace smn\pheeca\kernel\Template;

use \smn\pheeca\kernel\MVC\ViewInterface;
use \smn\pheeca\kernel\File;

/**
 * Description of Html
 *
 * @author Simone Esposito
 */
class Html implements ViewInterface {

    protected $_document;

    public function __construct($document = null, $content = null) {
        if (is_null($document)) {
            global $renderedPage;
            $this->_document = $renderedPage;
        } else {
            $this->_document = $document;
        }
        
        if (!is_null($content)) {
            if ($content instanceof File) {
                
            }
        }
        
        
    }

    public function getContent() {
        global $renderedPage;
        return $renderedPage->saveHTML();
        
        
    }

    public function outputContent() {
        $this->_templateClass->outputContent();
    }

}
