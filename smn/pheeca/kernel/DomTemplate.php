<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\MVC\ViewInterface;
use \smn\pheeca\kernel\File;

/**
 * Description of Template
 *
 * @author Simone
 */
class DomTemplate implements ViewInterface {

    protected $_document = null;

    public function __construct($document = null) {
        if (is_null($document)) {
            global $renderedPage;
            $this->_document = $renderedPage;
        }
        else {
            $this->_document = $document;
        }
    }

    public function getContent() {
        
    }

    public function outputContent() {
        
    }

    public static function getTemplateFromFile($file) {
        $content = new \DomDocument();
        if ($file instanceof File) {
            $content->loadHTMLFile($file->getFileName());
        } else {
            $content->loadHTML($file);
        }
        global $renderedPage;
        $renderedPage = $content;
        return new static($renderedPage);
    }

}
