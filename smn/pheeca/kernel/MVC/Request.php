<?php

namespace smn\pheeca\kernel\MVC;

use \smn\pheeca\kernel\MVC;
use \smn\pheeca\kernel\MVC\RequestInterface;
use \smn\pheeca\kernel\Events;

/**
 * Description of Request
 *
 * @author Simone Esposito
 */
class Request implements RequestInterface {

    const REQUEST_INSTANCE_CLASS = 'request-instance-class';

    protected $_mvc;
    protected $_request;
    protected $_cookie;
    protected $_get;
    protected $_post;
    protected $_fullUrl;

    public function __construct($mvc = null) {

        $this->_mvc = $mvc;

        $this->_request = $_SERVER;
        $this->_cookie = $_COOKIE;
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_fullUrl = $this->fullUrl();
    }

    /**
     * 
     * @return MVC
     */
    public function getMvcClass() {
        return $this->_mvc;
    }

    private final function fullUrl() {
        $scheme = $this->_request['REQUEST_SCHEME'];
        $serverName = $this->_request['SERVER_NAME'];
        $port = $this->_request['SERVER_PORT'];
        $url = $this->_request['REQUEST_URI'];
        return sprintf('%s://%s:%s%s', $scheme, $serverName, $port, $url);
    }

    public function cget($name) {
        if (array_key_exists($name, $this->_cookie)) {
            return $this->_cookie[$name];
        }
        return false;
    }

    public function pget($name) {
        if (array_key_exists($name, $this->_post)) {
            return $this->_post[$name];
        }
        return false;
    }

    public function qget($name) {
        // query string
        if (array_key_exists($name, $this->_get)) {
            return $this->_get[$name];
        }
        return false;
    }

    public function hget($name) {
        $key = 'HTTP_' . strtoupper($name);
        if (array_key_exists($key, $this->_request)) {
            return $this->_request[$key];
        }
        return false;
    }

    public function isAjax() {
        if (array_key_exists('HTTP_X_REQUESTED_WITH', $this->_request)) {
            return true;
        }
        return false;
    }

    public function isGet() {
        $method = $this->_request['REQUEST_METHOD'];
        if ($method == 'GET') {
            return true;
        }
        return false;
    }

    public function isPost() {
        $method = $this->_request['REQUEST_METHOD'];
        if ($method == 'POST') {
            return true;
        }
        return false;
    }

    public function getHttpProtocol() {
        return $this->_request['SERVER_PROTOCOL'];
    }

    public function getScheme() {
        $parse = parse_url($this->_fullUrl);
        $param = 'scheme';
        if (array_key_exists($param, $parse)) {
            return $parse[$param];
        }
        return false;
    }

    public function getRequestHost() {
        $parse = parse_url($this->_fullUrl);
        $param = 'host';
        if (array_key_exists($param, $parse)) {
            return $parse[$param];
        }
        return false;
    }

    public function getPort() {
        $parse = parse_url($this->_fullUrl);
        $param = 'port';
        if (array_key_exists($param, $parse)) {
            return $parse[$param];
        }
        return false;
    }

    public function getRequest() {
        $parse = parse_url($this->_fullUrl);
        $param = 'path';
        if (array_key_exists($param, $parse)) {
            return $parse[$param];
        }
        return false;
    }

    public function getFullRequest() {
        $parse = parse_url($this->_fullUrl);
        $param = 'query';
        if (array_key_exists($param, $parse)) {
            return $this->getRequest() . '?' . $parse[$param];
        }
        return $this->getRequest();
    }

    public function isSecureConnection() {
        if ($this->getScheme() == 'https') {
            return true;
        }
        return false;
    }

    /**
     * Restituisce l'ennesimo dell'ennesimo path presente nella richiesta
     * @param Integer $n
     * @return boolean|String False se non trova nulla, altrimenti il nome
     * del path alla posizione $n
     */
    public function nPath($n) {
        // se ci sono solo 2 elementi nella richiesta, restituisco false
        $pieces = explode('/', $this->getRequest());

        // cancello il primo elemento se è uguale a ''
        reset($pieces); // mi posiziono sul primo elemento
        // se il primo elemento è uno spazio, lo elimino
        if (current($pieces) == '') {
            unset($pieces[0]);
        }
        // se il totale degli elementi è minore di due, non esistono mai altri pezzi di richiesta
        if (count($pieces) == 0) {
            return false;
        }

        $paths = array_values($pieces);


        if (array_key_exists($n, $paths)) {
            return $paths[$n];
        }
        return false;
    }

    
    public function getScriptDirBase() {
        return dirname($this->_request['SCRIPT_NAME']);
    }
    
    /**
     * Restituisce lo script richiamato (quello usato da apache per la chiamata in caso di rewrite etc)
     * @return String
     */
    public function getScriptName() {
        return $this->_request['SCRIPT_NAME'];
    }
    
    /**
     * Restituisce l'url di base della chiamata. Se ad esempio il framework deve agire partendo dalla directory /blog/ , 
     * una chiamata /blog/pippo risulterà solo /pippo con questo metodo.
     * @return String
     */
    public function getRootRequest() {
        $e1 = explode('/', $this->getScriptDirBase());
        $e2 = explode('/', $this->_request['REQUEST_URI']);
        $diff = array_diff($e2, $e1);

        foreach ($diff as $key => $path) {
            if ($path == '') {
                unset($diff[$key]);
            }
        }

        if (empty($diff)) {
            $fullRequest = '/';
        } else {
            $fullRequest = '/' . implode('/', $diff);
        }
        return $fullRequest;
    }

    public function isNPathEqual($n, $name) {
        return ($this->nPath($n) == $name) ? true : false;
    }

}
