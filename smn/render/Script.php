<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of render_script
 *
 * @author Simone
 */
class render_script extends render_Element {

    public function __construct($href = null, $value = null, $namespaceURI = null, $parentDocument = null, $attributes = array()) {

        if (!is_null($href)) {
            $attributes['src'] = $href;
            $content = null;
        }

        if (!is_null($value)) {
            unset($attributes['src']); // cancello solo se esiste
            $content = $value;
        }

        if (!array_key_exists('type', $attributes)) {
            $attributes['type'] = 'text/javascript';
        }
        // se passo entrambi i parametri, do priorità al contenuto, altrimenti creo un link con l'href
        parent::__construct('script', $content, $namespaceURI, $parentDocument, null, $attributes);
    }
}
