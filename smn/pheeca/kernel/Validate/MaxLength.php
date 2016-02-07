<?php
namespace smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;

/**
 * Description of Validator_Int
 *
 * @author smn
 */
class MaxLength extends Validate {

    const TOO_MANY_CHAR = 'TOO_MANY_CHAR';

    protected $_messages = array(self::TOO_MANY_CHAR => 'La stringa %text% supera il numero massimo di caratteri (%maxlength%)');

    /**
     * Valore relativo alla lunghezza massima della stringa
     * @var Integer 
     */
    protected $_maxlength = 25;

    public function __construct($max = 25, $text = null) {
        parent::__construct($text);
        $this->setMaxLength($max);
    }

    /**
     * Imposta il numero massimo di caratteri che la stringa può assumere
     * @param Int $max
     */
    public function setMaxLength($max = 25) {
        if ($max >= 0) {
            $this->_maxlength = $max;
        }
    }

    /**
     * Restituisce la lunghezza massima di caratteri che la stringa può assumere
     * @return Integer
     */
    public function getMaxLength() {
        return $this->_maxlength;
    }

    public function validate() {
        if (strlen($this->get()) > $this->getMaxLength()) {
            $this->setState(false, self::TOO_MANY_CHAR);
        } else {
            $this->setState(true);
        }
    }
}
