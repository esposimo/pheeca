<?php
namespace smn\pheeca\kernel\HTMLElement;


use \smn\pheeca\kernel\HTMLElement;

/**
 * Description of Password
 *
 * @author Simone
 */
class Password extends HTMLElement {

    public function __construct($nodeValue = '', $attributes = array(), $childNodes = array()) {
        $attributes = array_merge(array('type' => 'password'), $attributes);
        parent::__construct('input', $nodeValue, $attributes, $childNodes);
    }

}
