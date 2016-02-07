<?php
namespace \smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;

/**
 * Description of String_Validator
 *
 * @author smn
 */
class ListOfValues extends Validate {

    const NOT_FOUND = 'NOT_FOUND';

    protected $_messages = array(self::NOT_FOUND => 'Il valore non è stato trovato');

    
    /**
     * Lista dei valori ammessi
     * @var Array 
     */
    protected $_values = array();


    public function __construct($evals = array(), $text = null) {
        parent::__construct($text);
        $this->_values = $evals;
    }

    /**
     * Imposta tutti i valori di $data
     * @param type $data
     */
    public function values($data = array()) {
        $this->_values = $data;
    }

    /**
     * Aggiunge un valore alla lista dei valori immessi
     * @param String $value
     */
    public function addValue($value) {
        if (is_array($value)) {
            $this->_values = $value;
        } else {
            $this->_values[] = $value;
        }
    }

    /**
     * Elimina un valore dalla lista dei valori ammessi
     * @param String $value
     */
    public function delValue($value) {
        if (array_search($value, $this->_values) !== false) {
            $key = array_search($value, $this->_values);
            unset($this->_values[$key]);
        }
    }

    public function validate() {
        $text = $this->_text;
        if (array_search($text, $this->_values) === false) {
            $this->setState(false, self::NOT_FOUND);
        } else {
            $this->setState(true);
        }
    }

}
