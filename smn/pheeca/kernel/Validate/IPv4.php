<?php
namespace \smn\pheeca\kernel\Validate;

use \smn\pheeca\kernel\Validate as Validate;


/**
 * Description of IPv4
 *
 * @author smn
 */
class IPv4 extends Validate {

    const NO_IPV4 = 'NO_IPV4';

    protected $_messages = array(self::NO_IPV4 => 'L\'ip %text% non è un IPv4');

    public function validate() {

        if (filter_var($this->get(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->setState(true);
        } else {
            $this->setState(false, self::NO_IPV4);
        }
    }

}
