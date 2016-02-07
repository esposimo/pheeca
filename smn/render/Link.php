<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Link
 *
 * @author Simone
 */
class render_Link extends render_Element {
    
    
    public function __construct($link, $name = '') {
        parent::__construct('a',$name);
        $this->setAttribute('href',$link);
    }
    
    
    
}
