<?php
namespace smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;
/**
 * Description of Validator_EmailAddress
 *
 * @author smn
 */
class EmptyText extends Validate {

    const EMPTY_TEXT = 'EMPTY_TEXT';
    
    protected $_messages = array(self::EMPTY_TEXT => 'La stringa indicata non è vuota');

    public function __construct($text = null) {
        parent::__construct($text);
    }

    public function validate() {
        if ($this->_text == '') {
            $this->setState(true);
        } else {
            $this->setState(false, self::EMPTY_TEXT);
        }
    }

}
