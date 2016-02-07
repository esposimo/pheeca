<?php
namespace smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;

/**
 * Description of StringValidate (si chiama così e non String perchè php7 non li accetta come nome)
 *
 * @author Simone
 */
class StringValidate extends Validate {
    
    const NO_STRING = 'NO_STRING';
    
    protected $_messages = array(self::NO_STRING => 'La variabile %text% non è una stringa');
    
    public function validate() {
        if (is_string($this->_text)) {
            $this->setState(true);
        }
        else {
            $this->setState(false, self::NO_STRING);
        }
    }
}
