<?php
namespace smn\pheeca\kernel\Variable;


use \smn\pheeca\kernel\Variable as Variable;

/**
 * Description of Xml
 *
 * @author Simone
 */
class Xml extends Variable {

    /**
     * Filename da parsare
     * @var String 
     */
    protected $_file;

    /**
     * Classe da utilizzare per il parse
     * @var String 
     */
    protected $_class_name = 'SimpleXMLElement';

    /**
     * Opzioni 
     * @see http://php.net/manual/en/libxml.constants.php
     * @var Int 
     */
    protected $_options = 0;
    protected $_ns = '';
    protected $_is_prefix = false;
    public $_attributes = array();

    /**
     * Imposta il file ed inizia il parsing
     * @param String $filename
     */
    public function file($filename) {
        if (is_readable($filename)) {
            $this->_file = $filename;
//            $xml = simplexml_load_file($this->_file, $this->_class_name, $this->_options, $this->_ns, $this->_is_prefix);
            $xml = simplexml_load_file($this->_file);
            $this->process($xml);
        }
    }

    public function string($string) {
        $this->_file = null;
        $xml = simplexml_load_string($string, $this->_class_name, $this->_options, $this->_ns, $this->_is_prefix);
        $this->process($xml);
    }

    /**
     * Processa i dati
     * @param type $data
     */
    public function process($data) {
        foreach ($data as $nodeName => $nodeValue) {
            // $nodeValue è sempre un oggetto
            if (($nodeValue->count() > 0) || (count($nodeValue->attributes()) > 0)) {
                // se ha figli o ha attributi, allora lo considero come oggetto da parsare
                // se ha attributi li metto in $_attributes

                $clone = new self();
                $clone->process($nodeValue);
                foreach ($nodeValue->attributes() as $attrName => $attrValue) {
                    $clone->setAttribute($attrName, $attrValue);
                }
                $this->$nodeName = $clone;
            } else {
                // altrimenti è una stringa
                $this->$nodeName = (String) $nodeValue;
            }
        }
    }

    
    /**
     * Restituisce true o false se l'attributo $attrName esiste o meno
     * @param type $attrName
     * @return boolean
     */
    public function hasAttribute($attrName) {
        if (array_key_exists($attrName, $this->_attributes)) {
            return true;
        }
        return false;
    }

    /**
     * Imposta l'attributo $attrName con il valore $attrValue. $attrValue viene forzato a String
     * @param type $attrName
     * @param type $attrValue
     * @return boolean
     */
    public function setAttribute($attrName, $attrValue = '') {
        $this->_attributes[$attrName] = (String) $attrValue;
        return true;
    }

    /**
     * Restituisce l'attributo $attrName, se non esiste restituisce false;
     * @param type $attrName
     * @return Mixed
     */
    public function getAttribute($attrName) {
        if (hasAttribute($attrName)) {
            return $this->_attributes[$attrName];
        }
        return false;
    }

    /**
     * Opzioni da utilizzare
     * @see http://php.net/manual/en/libxml.constants.php
     * @param Int $options
     */
    public function options($options = 0) {
        $this->_options = $options;
    }

    /**
     * Namespace da utilizzare
     * @param String $ns
     */
    public function ns($ns = '') {
        $this->_ns = $ns;
    }

    /**
     * True se NS è un prefisso, falso se è un URI. Di default è falso
     * @param Boolean $is_prefix
     */
    public function is_prefix($is_prefix = false) {
        $this->_is_prefix = $is_prefix;
    }

    /**
     * Classe da utilizzare per il parse
     * @param String $class_name
     */
    public function class_name($class_name = 'SimpleXMLElement') {
        $this->_class_name = $class_name;
    }

}
