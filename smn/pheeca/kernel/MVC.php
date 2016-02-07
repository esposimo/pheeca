<?php

namespace smn\pheeca\kernel;

use \smn\pheeca\kernel\MVC\Exception as MVCException;
use \smn\pheeca\kernel\MVC\Request;
use \smn\pheeca\kernel\MVC\RequestInterface;
use \smn\pheeca\kernel\MVC\Response;
use \smn\pheeca\kernel\MVC\ResponseInterface;
use \smn\pheeca\kernel\MVC\Controller;
use \smn\pheeca\kernel\MVC\ControllerInterface;
use \smn\pheeca\kernel\MVC\ControllerException;
use \smn\pheeca\kernel\MVC\View;
use \smn\pheeca\kernel\MVC\ViewInterface;
use \smn\pheeca\kernel\MVC\ViewException;

/**
 * Description of MVC
 *
 * @author Simone Esposito <simone.esposito1986@gmail.com>
 */
class MVC {

    /**
     *
     * @var \smn\pheeca\kernel\MVC\Request 
     */
    protected $_request;
    protected $_response;
    protected $_controller = null;
    protected $_controllerName;
    protected $_view;

    public function __construct(RequestInterface $request = null, ControllerInterface $controllerClass = null) {
        if (is_null($request)) {
            $request = new Request();
        }
        
        if (is_null($controllerClass)) {
            // se è nulla glielo creo con la logica <controller>/<action>
            $controllerClass = Controller::getControllerInstance($request);
        }
        

        $this->setRequestClass($request);
        $this->setControllerClass($controllerClass);
        //$this->setResponseClass($response);


        // se il controller  viene passato, utilizzo quello, altrimenti lo creo in base alla richiesta
        // se il controller non esiste, allora vaffanculo ti do l'eccezione e ne creo uno vuoto e to chiann tu

//        try {
//            if (!is_null($controllerClass)) {
//                $this->setControllerClass($controllerClass);
//                $this->getControllerClass()->setView($viewClass);
//            } else {
//                $this->setControllerClass(Controller::getControllerInstance($this->getRequestClass()));
//                $this->getControllerClass()->setView($viewClass);
//            }
//        } catch (ControllerException $ex) {
//            if ($ex->getCode() == Controller::CONTROLLER_NOT_FOUND) {
//                $this->setControllerClass(new Controller());
//            }
//        }
    }

    public function exec() {

        $this->getControllerClass()->run(); // eseguo il controller
        // butto fuori il contenuto
        echo $this->getControllerClass()->getContent();


        return;
        try {
            Events::trigger(self::CONTROLLER_CLASS_INSTANCE);
        } catch (ControllerException $ex) {
            if ($ex->getCode() == Controller::CONTROLLER_NOT_FOUND) {
                $this->getControllerClass()->errorControllerNotFound(); // invia un 404
            }
            if ($ex->getCode() == Controller::ACTION_NOT_FOUND) {
                $this->getControllerClass()->errorActionNotFound();
            }
        }
        try {
            $this->getResponseClass()->sendHeader();
            $this->getViewClass()->outputContent();
        } catch (ViewException $ex) {
            if ($ex->getCode() == View::TEMPLATE_NOT_FOUND) {
                $this->getControllerClass()->errorTemplateNotFound();
                $this->getViewClass()->outputContent(); // qua deve funzionare per forza
            }
        }
    }

    /**
     * Configura una request per la MVC
     * @param Request $request
     */
    public function setRequestClass(Request $request) {
        $this->_request = $request;
    }

    /**
     * Restituisce la request class
     * @return Request
     */
    public function getRequestClass() {
        return $this->_request;
    }

    /**
     * 
     * @param Response $response
     */
    public function setResponseClass(Response $response) {
        $this->_response = $response;
    }

    /**
     * 
     * @return Response
     */
    public function getResponseClass() {
        return $this->_response;
    }

    /**
     * 
     * @param Controller $controller
     */
    public function setControllerClass(Controller $controller) {
        $this->_controller = $controller;
    }

    /**
     * 
     * @return Controller
     */
    public function getControllerClass() {
        return $this->_controller;
    }

    /**
     * 
     * @param View $view
     */
//    public function setViewClass(ViewCore $view) {
//        $this->_view = $view;
//    }
//
//    /**
//     * 
//     * @return View
//     */
//    public function getViewClass() {
//        return $this->_view;
//    }
//
//    /**
//     * Imposta un prefisso di base per le chiamate, se ad esempio il framework parte da una chiamata come /blog/ o altro
//     * @param String $prefix
//     */
//    public function setPrefixPath($prefix = '/') {
//        
//    }

    
    /**
     * Esegue il controller con la request e la logica di base del controller.
     * Passando un controller diverso può cambiare tutta la logica
     * @return \static
     */
    public static function run() {
        $request = new Request();
        $controllerName = \smn\pheeca\kernel\MVC\Controller::getControllerName($request);
        $controller = '\smn\pheeca\controller\\' . strtolower($controllerName);
        $mvc = new static($request, new $controller($request));
        $mvc->exec();
        return $mvc;
    }

}
