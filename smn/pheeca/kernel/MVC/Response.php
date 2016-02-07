<?php

namespace smn\pheeca\kernel\MVC;

use \smn\pheeca\kernel\MVC\ResponseInterface;
use \smn\pheeca\kernel\Events;

/**
 * Description of Response
 *
 * @author Simone Esposito
 */
class Response implements ResponseInterface {

    const RESPONSE_SEND_HEADERS     = 'response-send-headers';
    
    
    /**
     *
     * @var \smn\pheeca\kernel\MVC 
     */
    protected $_mvc;
    
    
    /**
     * Http code da inviare
     * @var Integer 
     */
    protected $_httpCode = 200;

    /**
     * Lista headers da inviare
     * @var Array 
     */
    protected $_headers = array('Content-Type' => 'text/html; charset=utf-8');

    /**
     * Lista dei messaggi HTTP 
     * @var Array
     */
    protected $_messageCode = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    );


    public function __construct($mvc = null) {
        $this->_mvc = $mvc;
    }

    /**
     * Configura un header da inviare prima del contenuto della pagina
     * @param String $headerName
     * @param String $headerValue
     */
    public function setHeader($headerName, $headerValue = '') {
        $this->_headers[$headerName] = $headerValue;
    }

    /**
     * Restituisce un header presente nella lista degli header impostati
     * Se l'header non esiste, restituisce false
     * @param String $headerName
     * @return String|boolean
     */
    public function getHeader($headerName) {
        if (array_key_exists($headerName, $this->_headers)) {
            return $this->_headers[$headerName];
        }
        return false;
    }

    /**
     * Rimuove un header dalla lista degli header da inviare
     * @param String $headerName
     */
    public function removeHeader($headerName) {
        if ($this->getHeader($headerName)) {
            unset($this->_headers[$headerName]);
        }
    }

    /**
     * Configura l'http code da inviare
     * @param Integer $code
     */
    public function setHttpCode($code = 200) {
        $this->_httpCode = $code;
    }
    /**
     * Restituisce il codice della risposta che sarà inviato
     * @return Integer
     */
    public function getHttpCode() {
        return $this->_httpCode;
    }
    
    /**
     * 
     * @return \smn\pheeca\kernel\MVC
     */
    public function getMvcClass() {
        return $this->_mvc;
    }
    
    /**
     * 
     * @return \smn\pheeca\kernel\MVC\Request
     */
    public function getRequestClass() {
        return $this->getMvcClass()->getRequestClass();
    }

    /**
     * Invia tutti gli header pre-impostati
     */
    public function sendHeader() {
        Events::trigger(self::RESPONSE_SEND_HEADERS, array($this));
        $httpCode = $this->getHttpCode();
        $protocol = $this->getRequestClass()->getHttpProtocol();
        $message = $this->_messageCode[$httpCode];
        $headerResponse = $protocol . ' ' . $httpCode . ' ' . $message;
        header($headerResponse, true, $httpCode); // invio l'http code
        foreach ($this->_headers as $headerName => $headerValue) {
            // invio tutti gli header
            header($headerName . ': ' . $headerValue, true);
        }
        
        // il cnotroller dirà alla vista di inviare il contenuto
    }

}
