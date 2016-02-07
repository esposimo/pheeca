<?php
namespace render;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of render_Collect
 *
 * @author Simone
 */

class render_Collect {

    protected $_elementsList = array();

    public function __construct($elements) {
        foreach($elements as $element) {
            $this->_elementsList[] = render_Element::createRenderElement($element);
        }
    }

    public function each($callback,$parameters = array()) {
        foreach($this->_elementsList as $element) {
            //$function = new ReflectionFunction($callback);
            //$function->invokeArgs(array_merge(array($element), $parameters));
            call_user_func_array($callback,array_merge(array($element),$parameters));
        }
    }
    
    public function __call($name, $arguments) {
        foreach($this->_elementsList as $element) {
            // per ogni render_element, chiamo il metodo e passo gli attributi
            call_user_func_array(array($element, $name), $arguments);
        }
    }
    
    public function getList() {
        return $this->_elementsList;
    }

}