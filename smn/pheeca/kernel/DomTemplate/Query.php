<?php
namespace smn\pheeca\kernel\DomTemplate;

use \smn\pheeca\kernel\DomTemplate\Element;
use \smn\pheeca\kernel\DomTemplate\Collect;

class Query {

    protected $_elementList;
    protected $_document;

    public function __construct($data = '*', $document = null) {
        global $renderedPage;
        if (is_null($document)) {
            $this->_document = $renderedPage;
        } else {
            $this->_document = $document;
        }

        if (is_string($data)) {
            $selector = self::css2xpath($data);
            $xPath = new \DOMXPath($this->_document);
            $this->_elementList = $xPath->query($selector);
        }

        if ($data instanceof \DOMNodeList) {
            $this->_elementList = $data;
        }
        
    }

    public function __call($name, $arguments) {
        $i = 0;
        while ($i < $this->_elementList->length) {
            $element = $this->_elementList->item($i);
            call_user_func_array(array($element, $name), $arguments);
            $i++;
        }
    }

    /**
     * 
     * @param type $selector
     * @param type $document
     * @return Element|Collect
     */
    public static function query($selector, $document = null) {
        $self = new self($selector, $document);
        return $self->getElements(); // iterator ?
    }

    /**
     * 
     * @return \render_Collect|render_Element
     */
    private function getElements() {
        if ($this->_elementList->length == 0) {
            return null;
        }
        if ($this->_elementList->length == 1) {
            $element = $this->_elementList->item(0);
            return Element::createRenderElement($element);
        }
        return new Collect($this->_elementList);
    }

    public static function css2xpath($selector) {
        $selector = ' ' . $selector;
        // from a javascript idea of http://james.padolsey.com/scripts/javascript/css2xpath.js
        // convert in php by me
        $regex = array(
            /* All blocks of 2 or more spaces */
            array('/\s{2,}/', function() {
                    return ' ';
                }),
            /* additional selectors (comma seperated) */
            array('/\s*,\s*/', function() {
                    return '|//';
                }),
            /* Attribute selectors */
            array('/[\s\/]?\[([^\]]+)\]/', function($param) {
                    $m = $param[0];
                    $kv = $param[1];
                    return ((substr($m, 0, 1) == ' ') ? '*' : ' ') . '[@' . $kv . ']';
                }),
            /* :nth-child(n) */
            array('/:nth-child\((\d+)\)/', function($param) {
                    $m = $param[0];
                    $n = $param[1];
                    return '[' . $n . ']';
                }),
            /* :last-child */
            array('/:last-child/', function($param) {
                    $m = $param[0];
                    $n = $param[1];
                    return '[last()]';
                }),
            /* :first-child */
            array('/:first-child/', function($param) {
                    $m = $param[0];
                    $n = $param[1];
                    return '[1]';
                }),
            /* "sibling" selectors */
            array('/\s*\+\s*([^\s]+?)/', function($param) {
                    $m = $param[0];
                    $sib = $param[1];
                    return '/following-sibling::' . $sib . '[1]';
                }),
            /* "child" selectors */
            array('/\s*>\s*/', function() {
                    return '/';
                }),
            /* Remaining Spaces */
            array('/\s/', function() {
                    return '';
                }),
            /* #id */
            array('/([a-z0-9]?)#([a-z][-a-z0-9_]+)/', function($param) {
                    $m = $param[0];
                    $pre = $param[1];
                    $id = $param[2];
                    return $pre . (preg_match('/^[a-z0-9]/', $m) ? '' : '*') . '[@id=\'' . $id . '\']';
                }),
            /* .className */
            array('/([a-z0-9]?)\.([a-z][-a-z0-9_-]+)/', function($param) {
                    $m = $param[0];
                    $pre = $param[1];
                    $cls = $param[2];
                    return $pre . (preg_match('/^[a-z0-9]/', $m) ? '' : '*') . '[contains(concat(\' \',@class,\' \'),\' ' . $cls . ' \')]';
                })
        );

        foreach ($regex as $rx) {
            $selector = preg_replace_callback($rx[0], $rx[1], $selector);
        }
        return (preg_match('/^\/\//', $selector)) ? $selector : '//' . $selector;
    }

}

// render_Query::query('body')->each(function() { $this->setAttribute('prova'); });


