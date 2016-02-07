<?php

namespace smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;

/**
 * Description of Int
 *
 * @author smn
 */
class Integer extends Validate {

    const NO_INT_VALUE = 'NO_INT_VALUE';

    protected $_errorsCode = array(
        self::NO_INT_VALUE => 10
    );
    protected $_messages = array(self::NO_INT_VALUE => 'Il valore %text% non è un intero');

    public function validate() {
        if (is_numeric($this->get())) {
            $this->setState(true);
        } else {
            $this->setState(false, self::NO_INT_VALUE);
        }
    }

}
