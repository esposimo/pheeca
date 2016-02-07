<?php
namespace \smn\pheeca\kernel\Validate;


use \smn\pheeca\kernel\Validate as Validate;
/**
 * Description of IPv4
 *
 * @author smn
 */
class IPv6 extends Validate {

    const NO_IPV6 = 'NO_IPV6';

    protected $_messages = array(self::NO_IPV6 => 'L\'ip %text% non è un IPv6');

    public function validate() {
        if (filter_var($this->get(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->setState(true);
        } else {
            $this->setState(false, self::NO_IPV6);
        }
    }

}
