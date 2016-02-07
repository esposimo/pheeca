<?php
namespace smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;

/**
 * Description of Core
 *
 * @author Simone
 */
class Regex extends Validate {

    protected $regex;

    const NO_REGEX = 'NO_REGEX';

    public function __construct($text = null, $regex = null) {
        parent::__construct($text);
        if (!is_null($regex)) {
            $this->setRegex($regex);
        }
        $this->addMessage(self::NO_REGEX, 'La stringa %text% non matcha con la regular expression %regex%');
        $this->addPattern('%regex%', $this->getRegex());
    }
    
    public function setRegex($regex) {
        $this->regex = $regex;
    }

    public function getRegex() {
        return $this->regex;
    }

    public function validate() {
        if (preg_match($this->regex, $this->get())) {
            $this->setState(true);
        } else {
            $this->setState(false, self::NO_REGEX);
            //$message = $this->_errorCode .': ' .$this->getMessage($this->_errorCode);
            //throw new Regex_ExceptionCore($message, null, new \kernel\Validate\Exception());
            //throw new Regex_ExceptionCore($message);
        }
    }

}
