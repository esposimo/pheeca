<?php
namespace smn\pheeca\kernel\MVC;

/**
 * Description of Exception
 *
 * @author Simone
 */
class ViewException extends \Exception {
    
    public function __construct($message = '', $code = 0, $previous = null) {
        $message = 'ViewException: '. $message;
        parent::__construct($message, $code, $previous);
    }
    
}
