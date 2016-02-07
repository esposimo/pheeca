<?php
namespace smn\pheeca\kernel\MVC;

use \smn\pheeca\kernel\MVC;
use \smn\pheeca\kernel\HTMLElement;
use \smn\pheeca\kernel\MVC\ControllerException;
use \smn\pheeca\kernel\MVC\ControllerInterface;


/**
 * Description of Controller
 *
 * @author Simone
 */
class Controller implements ControllerInterface {

    
    const CONTROLLER_CONFIGURE      = 'controller-configure';
    const CONTROLLER_RUN_ACTION     = 'controller-run-action';
    const CONTROLLER_POST_ACTION    = 'controller-post-action';
    
    
    
    /**
     * Constante d'errore per controller non trovato
     */
    const CONTROLLER_NOT_FOUND = 1;

    /**
     * Constante d'errore per action non trovata
     */
    const ACTION_NOT_FOUND = 2;

    /**
     *
     * @var \smn\pheeca\kernel\MVC|\smn\pheeca\kernel\MVCInterface
     */
    protected $_mvc;

    /**
     * Controller di default
     */
    protected static $_defaultControllerName = 'index';

    /**
     * Regular expression per intercettare il controller
     * @var String 
     */
    protected static $_defaultRegexController = '^/([A-Za-z0-9_-]+)/?.*^';

    /**
     * Action di default
     * @var String
     */
    protected $_defaultAction = 'index';

    /**
     * Regular expression per intercettare la action
     * @var String 
     */
    protected $_defaultRegexAction = '^/[A-Za-z0-9_-]+/([A-Za-z0-9_-]+)/?.*^';

    /**
     * Linguaggio di default
     * @var String 
     */
    protected $_defaultLanguage = 'it';

    /**
     * Regex per vedere quale è il linguaggio da considerare
     * @var String 
     */
    protected $_defaultRegexLanguage = '^/.*\?(.*)?(&?lang=[A-Za-z_-]+)(.*)?^';

    /**
     *
     * @var View 
     */
    protected $_view;

    
    
    /**
     * Richiesta assegnata al controller
     * @var Request 
     */
    protected $_request;
    /**
     * 
     * @param MVC $mvc
     */
    
    
    
    /**
     * Namespace del controller. Di default nella directory pheeca
     * @var String 
     */
    protected static $_controllerNamespace = '\smn\pheeca\controller\\';
    
    
    
    
    /**
     * Directory dei templates alla quale viene aggiunto controller ed action
     * Può non essere considerata se si usa un controller custom
     * @var String 
     */
    protected static $_templateDirectory = './templates';
    
    
    /**
     * Nome del controller
     * @var String 
     */
    protected $_controllerName = '';
    
    public function __construct(Request $request) {
        
        $this->_request = $request;
        $this->_controllerName = self::getControllerName($request);
        
        
//        $this->_mvc = $mvc;
//        $view = new View($mvc);
//        $this->_view = $view;
//        $this->getMvcClass()->setViewClass($view);


//        $controllerName = self::getControllerName($mvc);
//        $action = $this->getActionName();
//        $file = $view->getTemplatePath() . '/' . $controllerName . '/' . $action . '.html';
//        $view->setTemplateFile($file);
    }

    /**
     * Restituisce il nome del controller di default
     * @return String
     */
    public static function getDefaultController() {
        return self::$_defaultControllerName;
    }

    /**
     * Configura il nome del controller di default
     * @param String $defaultController
     */
    public static function setDefaultController($defaultController) {
        self::$_defaultControllerName = $defaultController;
    }

    /**
     * 
     * @param String $defaultRegexController
     */
    public static function setDefaultRegexController($defaultRegexController = '^/([A-Za-z0-9_-]+)/?.*^') {
        self::$_defaultRegexController = $defaultRegexController;
    }

    /**
     * 
     * @return String
     */
    public static function getDefaultRegexController() {
        return self::$_defaultRegexController;
    }

    /**
     * Restituisce il controller in base alla richiesta
     * @return String
     */
    public static function getControllerName(Request $mvc) {
        
        // se il framework non parte dalla document_root, potresti sputtanarti con i controller in quanto per trovare il controller
        // hai bisogno della request_uri, che contiene la richiesta completa a partire dalla document root
        // quindi mi calcolo la richiesta senza considerare eventuali sottodirectory della document root
        
        
        // Il controller non deve interessarsi del fatto che il sito stia nella documentroot o meno
        // // l'importante è che viene istanziato e sa che azione intraprendere .. 
        // la richiesta ovviamente eliminerà eventuali path precedenti alla chiamata dove il framework (o cms?) parte
     
        preg_match(self::$_defaultRegexController, $mvc->getRootRequest(), $m);
        
        if (empty($m)) {
            return self::getDefaultController();
        }
        return $m[1];
    }

    /**
     * Restituisce l'istanza controller corretta in base alla logica di
     * ritrovamento del controller
     * @param MVC $mvc
     * @return \smn\pheeca\kernel\Controller
     */
    public static function getControllerInstance(Request $mvc) {
        $name = self::getControllerName($mvc);
        // carico i controller da una directory che equivale al namespace
        // di default il namespace si trova nella directory controller , all'altezza del kernel
        $class = self::$_controllerNamespace .strtolower($name);
        if (!class_exists($class, true)) {
            //$mvc->setControllerClass(new \smn\pheeca\kernel\MVC\Controller($mvc));
            throw new ControllerException('Controller inesistente', self::CONTROLLER_NOT_FOUND);
        }
        return new $class($mvc);
    }

    /**
     * Configura il nome della action di default
     * @param String $action
     */
    public function setDefaultAction($action = 'index') {
        $this->_defaultAction = $action;
    }

    /**
     * Restituisce il nome della action di default
     * @return String
     */
    public function getDefaultAction() {
        return $this->_defaultAction;
    }

    /**
     * Configura la regular expression per inercettare la action
     * @param String $defaultRegexAction
     */
    public function setDefaultRegexAction($defaultRegexAction = '^/[A-Za-z0-9_-]+/([A-Za-z0-9_-]+)/?.*^') {
        $this->_defaultRegexAction = $defaultRegexAction;
    }

    /**
     * Restituisce la regular expression configuata per intercettare la action
     * @return String
     */
    public function getDefaultRegexAction() {
        return $this->_defaultRegexAction;
    }

    /**
     * Restituisce il nome della action in base alla chiamata
     * @return String
     */
    public function getActionName() {
        $m = array();
        preg_match($this->_defaultRegexAction, $this->_request->getScriptDirBase(), $m);

        if (empty($m)) {
            return $this->getDefaultAction();
        }
        return $m[1];
    }

    /**
     * 
     * @return MVC
     */
//    public function getMvcClass() {
//        return $this->_mvc;
//    }
//
//    /**
//     * 
//     * @return Request
//     */
//    public function getRequestClass() {
//        return $this->getMvcClass()->getRequestClass();
//    }
//
//    /**
//     * 
//     * @return Response
//     */
//    public function getResponseClass() {
//        return $this->getMvcClass()->getResponseClass();
//    }
//
//    /**
//     * 
//     * @return View
//     */
//    public function getViewClass() {
//        return $this->getMvcClass()->getViewClass();
//    }
    
    
    
    public static function setControllerNamespace($controllerNamespace) {
        self::$_controllerNamespace = $controllerNamespace;
    }
    
    public static function getControllerNamespace() {
        return self::$_controllerNamespace;
    }
    
    
    public static function setTemplateDirectory($templateDirectory) {
        self::$_templateDirectory = $templateDirectory;
    }
    
    public static function getTemplateDirectory() {
        return self::$_templateDirectory;
    }
    
    

    /**
     * 
     */
    public function run() {
        // al run del controller, genero il template
        //
        
        
        // il template viene creato in base al nome e l'action intrapresa (comportamento di default)
        //
        //
        
        // questo metodo imposta la vista
        // la vista è una classe (View) al quale viene passato un parametro
        // questo parametro deve essere una classe con interfaccia ViewInterface, che permette al controller di gestire appunto il template
        
        $this->configureView();
        $method = $this->getActionName();
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            throw new ControllerException('Action non presente', self::ACTION_NOT_FOUND);
        }
    }

    public function configureView() {
        $controller = $this->_controllerName;
        $action = $this->getActionName();
        $file = self::$_templateDirectory .'/' .$controller .'/' .$action .'.html';
        $this->setView(new View($file));
    }
    
    
    public function getContent() {
        return $this->getView()->getContent();
    }


    public function error() {
        $this->setPageNotFound();
    }

    public function errorControllerNotFound() {
        $this->setPageNotFound();
        $html = new HTMLElement('html');
        $html->addChild('head', $head = new HTMLElement('head'));
        $html->addChild('body', $body = new HTMLElement('body'));
        $body->addChild('h1', $p = new HTMLElement('h1'));
        $p->setValue('Controller non trovata');
        $this->getViewClass()->setTemplate($html);
    }

    public function errorActionNotFound() {
        $this->setPageNotFound();
        $html = new HTMLElement('html');
        $html->addChild('head', $head = new HTMLElement('head'));
        $html->addChild('body', $body = new HTMLElement('body'));
        $body->addChild('h1', $p = new HTMLElement('h1'));
        $p->setValue('Action non trovato');
        $this->getViewClass()->setTemplate($html);
    }

    public function errorTemplateNotFound() {
        $this->setInternalServerError();
        $html = new HTMLElement('html');
        $html->addChild('head', $head = new HTMLElement('head'));
        $html->addChild('body', $body = new HTMLElement('body'));
        $body->addChild('h1', $p = new HTMLElement('h1'));
        $p->setValue('Template non trovata<?php echo \'we\'; ?>');
        $this->getViewClass()->setTemplate($html);
    }

    /**
     * Content Type da inviare
     * @param String $contentType
     */
    public function setContentType($contentType = 'text/html') {
        $ct = explode(';', $this->getResponseClass()->getHeader('Content-Type'));
        $ct[0] = $contentType;
        $this->getResponseClass->setHeader('Content-Type', implode('; ', $ct));
    }

    /**
     * Restituisce il content type che sarà inviato
     * @return String
     */
    public function getContentType() {
        $ct = explode(';', $this->getResponseClass()->getHeader('Content-Type'));
        return $ct[0];
    }

    /**
     * Imposta come content type 'application/json'
     */
    public function setContentTypeJson() {
        $this->getResponseClass()->setHeader('Content-Type', 'application/json');
    }

    /**
     * Imposta il charset indicato da inviare
     * @param String $charset
     */
    public function setCharSet($charset = 'utf-8') {
        $ct = explode(';', $this->getResponseClass()->getHeader('Content-Type'));
        $ct[1] = 'charset=' . $charset;
        $this->getResponseClass()->setHeader('Content-Type', implode('; ', $ct));
    }

    public function removeCharset() {
        $ct = explode(';', $this->getResponseClass()->getHeader('Content-Type'));
        unset($ct[1]);
        $this->getResponseClass()->setHeader('Content-Type', implode('; ', $ct));
    }

    /**
     * Restituisce il charset configurato
     * @return String
     */
    public function getCharSet() {
        $ct = explode(';', $this->getResponseClass()->getHeader('Content-Type'));
        if (isset($ct[1])) {
            $cs = explode('=', $ct[1]);
            return $cs[1];
        }
        return false;
    }

    /**
     * Imposta un redirect 301 sulla location $location
     * @param String $location
     */
    public function setRedirect301($location) {
        $this->getResponseClass()->setHttpCode(301);
        $this->getResponseClass()->setHeader('Location', $location);
    }

    /**
     * Imposta un redirect 302 sulla location $location
     * @param String $location
     */
    public function setRedirect302($location) {
        $this->getResponseClass()->setHttpCode(302);
        $this->getResponseClass()->setHeader('Location', $location);
    }

    /**
     * Imposta l'http code su 404 not found
     */
    public function setPageNotFound() {
        $this->getResponseClass()->setHttpCode(404);
    }

    /**
     * Imposta l'http code su 503 
     */
    public function setInternalServerError() {
        $this->getResponseClass()->setHttpCode(503);
    }

    /**
     * Imposta l'http code su 200 OK
     */
    public function setHttpOk() {
        $this->getResponseClass()->setHttpCode(200);
    }

    public function getView() {
        return $this->_view;
    }

    public function setView($viewClass) {
        $this->_view = $viewClass;
    }

}
