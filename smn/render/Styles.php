<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of render_styles
 *
 * @author Simone
 */


class render_styles extends render_Element {

    public function __construct($href = null, $value = null, $namespaceURI = null, $parentDocument = null, $attributes = null) {
        
        if (!is_null($href)) {
            $tagName = 'link';
            $attributes = array('rel' => 'stylesheet', 'href' => $href);
            $content = null;
        }
        
        if (!is_null($value)) {
            $tagName = 'style';
            $attributes = array('type' => 'text/css');
            $content = $value;
        }
        // se passo entrambi i parametri, do priorità al contenuto, altrimenti creo un link con l'href
        parent::__construct($tagName, $content, $namespaceURI, $parentDocument, null, $attributes);
    }
}