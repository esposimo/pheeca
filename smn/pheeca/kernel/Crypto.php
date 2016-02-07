<?php
namespace smn\pheeca\kernel;


/**
 * Description of Crypto
 *
 * @author Simone Esposito
 */
class Crypto {
    
    protected $_secure_key;
    
    protected $_iv;
    
    public function __construct($secure_key , $iv = null) {
        $this->_secure_key = $secure_key;
        $this->_iv = $iv;
    }
    
    
    // base cript method
    
    public function crypt($data) {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_secure_key, $data, MCRYPT_MODE_CBC, $this->_iv);
    }
    
    
    public function decrypt($data) {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_secure_key, $data, MCRYPT_MODE_CBC, $this->_iv);
    }
    
}
