<?php
namespace smn\pheeca\kernel\Validate;


use \smn\kernel\Validate\Regex as Regex;



/**
 * Description of EmailAddress
 *
 * @author Simone
 */
class EmailAddress extends Regex {
    
    protected $_regex = '/^[A-Za-z0-9\.-_]+@[A-Za-z0-9\.-_]+\.[A-Za-z]+$/';
    const NO_VALID_EMAIL = 'NO_VALID_EMAIL';
    
    public function __construct($text = null) {
        parent::__construct($text);
        $this->addMessage(self::NO_VALID_EMAIL, 'L\'indirizzo %text% non è una valida email');
    }
    
    public function validate() {
        if (preg_match($this->getRegex(),$this->get())) {
            $this->setState(true);
        }
        else {
            $this->setState(false, self::NO_VALID_EMAIL);
        }
    }
}
