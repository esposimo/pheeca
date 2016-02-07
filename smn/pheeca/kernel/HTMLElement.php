<?php
namespace smn\pheeca\kernel;


use \smn\pheeca\override\kernel\Events;

/**
 * Description of HTMLElement
 *
 * @author Simone
 */
class HTMLElement {

    const ELEMENT_HTML_INSTANCE     =   'element-html-instance';
    
    
    /**
     * Nome del tag
     * @var String 
     */
    protected $_tagname;

    /**
     * Lista degli attributi dell'elemento
     * @var Array 
     */
    protected $_attributes = array();

    /**
     * Lista degli elementi figli, deve essere un array di HTMLElementCore
     * @var Array 
     */
    protected $_childNodes = array();

    /**
     * Contenuto del tag
     * @var String 
     */
    protected $_nodeValue;

    /**
     * Se l'elemento non ha nodeValue e non ha figli e la variabile è true, viene chiuso il tag nella forma breve, es <br />
     * @var Boolean 
     */
    protected $_emptyTag = false;

    public function __construct($tagname, $nodeValue = '', $attributes = array(), $childNodes = array()) {
        $this->setName($tagname);
        $this->setValue($nodeValue);
        foreach($attributes as $attrName => $attrValue) {
            $this->addAttribute($attrName, $attrValue);
        }
        foreach($childNodes as $childName => $childElement) {
            $this->addChild($childName, $childElement);
        }
        Events::trigger(self::ELEMENT_HTML_INSTANCE, array($this));
    }

    /**
     * Configura il nome del tag
     * @param String $tagname
     */
    public function setName($tagname) {
        $this->_tagname = $tagname;
    }

    /**
     * Restituisce il nome del tag
     * @return String
     */
    public function getName() {
        return $this->_tagname;
    }

    /**
     * Configura l'empty tag per la chiusura breve. True il tag viene chiuso in forma breve (se non ha nodeValue e children)
     * @param Boolean $empty
     */
    public function setEmptyTag($empty) {
        $this->_emptyTag = $empty;
    }

    /**
     * Restituisce il valore dell'empty tag, true/false
     * @return Boolean
     */
    public function getEmptyTag() {
        return $this->_emptyTag;
    }

    /**
     * Inserisce un figlio all'elemento. E' necessario indicare un nome per fare eventuali rimozioni
     * @param String $name
     * @param self $element
     */
    public function addChild($name, $element) {
        if ($element instanceof self) {
            $this->_childNodes[] = array('name' => $name, 'element' => $element);
        }
    }

    /**
     * Restituisce l'elemento figlio avente nome $name. Se non esiste, restituisce false
     * @param String $name
     * @return boolean|self
     */
    public function getChild($name) {
        $return = false;
        array_walk($this->_childNodes, function($value, $key, $data) {
            if ($value['name'] == $data[0]) {
                $data[1] = $this->_childNodes[$key];
            }
        }, array($name, &$return));
        return $return;
    }

    /**
     * Rimuove un figlio avente nome $name
     * @param String $name
     */
    public function removeChild($name) {
        array_walk($this->_childNodes, function($value, $key, $name) {
            if ($value['name'] == $name) {
                unset($this->_childNodes[$key]);
            }
        }, $name);
    }

    /**
     * Configura il contenuto del nodo
     * @param String $nodeValue
     */
    public function setValue($nodeValue = '') {
        $this->_nodeValue = $nodeValue;
    }

    /**
     * Restituisce il contenuto del nodo, senza figli
     * @return String
     */
    public function getValue() {
        return $this->_nodeValue;
    }

    /**
     * Configura l'attributo $name con valore $value
     * @param String $name
     * @param String $value
     */
    public function addAttribute($name, $value = '') {
        $this->_attributes[$name] = $value;
    }

    /**
     * Restituisce true o false se l'attributo $name esiste 
     * @param String $name
     * @return boolean
     */
    public function hasAttribute($name) {
        if (array_key_exists($name, $this->_attributes)) {
            return true;
        }
        return false;
    }

    /**
     * Restituisce il valore dell'attributo $name. Se l'attributo non esiste, restituisce false
     * @param String $name
     * @return String|Boolean
     */
    public function getAttribute($name) {
        if ($this->hasAttribute($name)) {
            return $this->_attributes[$name];
        }
        return false;
    }
    
    /**
     * Elimina tutti gli attributi
     */
    public function clearAttributes() {
        $this->_attributes = array();
    }
    
    /**
     * Elimina tutti i figli
     */
    public function clearChildren() {
        $this->_childNodes = array();
    }
    

    public function toString() {
        $buffer = '';

        $buffer = '<' . $this->getName();
        // aggiungo gli attributi
        array_walk($this->_attributes, function($attrValue, $attrName, $b) {
            if ($attrValue == '') {
                $b[0] .= ' ' . $attrName;
            } else {
                $b[0] .= ' ' . $attrName . '="' . $attrValue . '"';
            }
        }, array(&$buffer));

        // aggiungo il valore
        if ((empty($this->_childNodes)) && ($this->getEmptyTag() == true) && ($this->getValue() == '')) {
            // se non ha figli e non ha contenuto ed emptytag è true, faccio la chiusura breve
            $buffer .= ' />';
        }
        // altrimenti aggiungo il node value
        else {
            $buffer .= '>';
            $buffer .= $this->getValue();
            array_walk($this->_childNodes, function($child, $key, $b) {
                $b[0] .= $child['element']->toString();
            }, array(&$buffer));
            $buffer .= '</' .$this->getName() .'>';
        }
        return $buffer;
        // chiudo il tag
    }

    public function out() {
        echo $this->toString();
    }
    
    public function __toString() {
        return $this->toString();
    }

}
