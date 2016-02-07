<?php
namespace smn\pheeca\kernel\HTMLElement;

use \smn\pheeca\kernel\HTMLElement;

/**
 * Description of TextBox
 *
 * @author Simone
 */
class TextBox extends HTMLElement {
    
    public function __construct($nodeValue = '', $attributes = array(), $childNodes = array()) {
        $attributes = array_merge(array('type' => 'text'), $attributes);
        parent::__construct('input', $nodeValue, $attributes, $childNodes);
    }
    
}
