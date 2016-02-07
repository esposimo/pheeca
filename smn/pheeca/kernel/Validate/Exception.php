<?php
namespace smn\pheeca\kernel\Validate;

/**
 * Description of Exception
 *
 * @author Simone
 */
class Exception extends \Exception {
    
    public function __construct($message = null, $code = null, $previous = null) {
        if (is_null($message)) {
            $message = \kernel\Translate::_('Errore nella validazione');
        }
        parent::__construct($message, $code, $previous);
    }

    
}
