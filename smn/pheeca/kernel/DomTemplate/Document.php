<?php
namespace smn\pheeca\kernel\DomTemplate;

use \smn\pheeca\kernel\DomTemplate\Query;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of render_Document
 *
 * @author Simone
 * 
 * 
 * 
 * QUESTA NON SERVE PIU
 * 
 */
class Document {

    protected $_document;

    public function __construct() {
        $this->_document = new \DomDocument();
    }
    

    public function aboveScripts() {
        $xPathSelector = render_Query::css2xpath('script');
        $xPath = new \DOMXPath($this->_document);
        $nodeList = $xPath->query($xPathSelector);

        $nodeListBody = $xPath->query(Query::css2xpath('body'));
        $body = $nodeListBody->item(0);

        $i = 0;
        while ($i < $nodeList->length) {
            $element = $nodeList->item($i);
            $body->appendChild($element);
            $i++;
        }
    }

    public function css_in_head() {
        $xPath = new \DOMXPath($this->_document);
        $nodeList = $xPath->query(Query::css2xpath('styles , link[rel="stylesheet"]'));
        $nodeListHead = $xPath->query(Query::css2xpath('head'));
        $head = $nodeListHead->item(0);
        $i = 0;

        while ($i < $nodeList->length) {
            $element = $nodeList->item($i);
            $head->appendChild($element);
            $i++;
        }
    }
    
    public function _output() {
        return $this->_document->saveHTML();
    }

    public function __toString() {
        return $this->_output();
    }
    
    
    public static function out() {
        $buffer = new self();
        echo $buffer->_output();
    }
    public static function htmlOut() {
        $buffer = new self();
        return $buffer->_document->saveHTML();
    }

}
