<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Checkbox
 *
 * @author Simone
 */
class render_Checkbox extends render_Element {
    
    public function __construct() {
        parent::__construct('input');
        $this->setAttribute('type','checkbox');
    }
    
}
