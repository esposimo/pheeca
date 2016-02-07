<?php
namespace smn\pheeca\kernel\MVC;

/**
 * Description of Exception
 *
 * @author Simone
 */
class ControllerException extends \Exception {
    
    public function __construct($message = '', $code = 0, $previous = null) {
        $message = 'ControllerException: '. $message;
        parent::__construct($message, $code, $previous);
    }
    
}
