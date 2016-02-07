<?php

namespace smn\pheeca\kernel\MVC;

use \smn\pheeca\kernel\MVC;
use \smn\pheeca\kernel\MVC\ViewInterface;
use \smn\pheeca\kernel\MVC\ViewException;
use \smn\pheeca\kernel\Variable;
use \smn\pheeca\kernel\Events;
use \smn\pheeca\kernel\Buffer;

/**
 * Description of Template
 *
 * @author Simone
 */
class View implements ViewInterface {

    const VIEW_INSTANCE_CLASS = 'view-instance-class';
    const VIEW_OUTPUT_CONTENT = 'view-output-content';

    /**
     * Constante d'errore per template non trovato
     */
    const TEMPLATE_NOT_FOUND = 1;

    /**
     *
     * @var \smn\pheeca\kernel\MVC|\smn\pheeca\kernel\MVCInterface
     */
    protected $_mvc;

    /**
     *
     * @var String 
     */
    protected $_template_path = './templates';

    /**
     * 
     * @var String 
     */
    protected $_template;

    /**
     *
     * @var type 
     */
    protected $_data;

    /**
     * 
     * @param MVC $mvc
     */
    public function __construct($template_file) {
        
        $this->_template = $template_file;
        $this->_data = new Variable();
    }

    public function __get($name) {
        return $this->_data->$name;
    }

    /**
     * Imposta la variabile $name con il valore $value da usare nel template
     * @param String $name
     * @param String $value
     * @return \TemplateCore
     */
    public function __set($name, $value = '') {
        $this->_data->$name = $value;
        return $this;
    }

    /**
     * Configura il path dei template
     * @param String $path
     */
    public function setTemplatePath($path) {
        $this->_template_path = $path;
    }

    /**
     * 
     * @return String
     */
    public function getTemplatePath() {
        return $this->_template_path;
    }

    public function setTemplateFromFile($file, $path = null) {
        $this->setTemplate($file, $path);
    }

    /**
     * La classe inviata dovrà avere due metodi fondamentali
     * Uno per restituire il contenuto sottoforma di stringa
     * Uno per restituire il contenuto sottoforma di file
     * @param TemplateInterface $class
     */
    public function setTemplateFromClass($class) {
        $this->_template = $class;
    }

    /**
     * 
     */
    public function setTemplateFileFromController() {
        $file = Controller::getControllerName($this->getMvcClass()) . '/' . $this->getControllerClass()->getActionName() . '.html';
        $this->setTemplateFile($file);
    }

    /**
     * Configura un template da una classe o una stringa che indica il nome di un file
     * @param Mixed $template Se è una stringa, lo vede come file e lo configura anteponendo $template_path (se esiste). Se è una classe
     * configura la classe come template
     * @param String $template_path Se indicato, lo antepone al parametro $template nel caso in cui $template sia una stringa
     */
    public function setTemplate($template, $template_path = null) {
        if (is_string($template)) {
            if (is_null($template_path)) {
                $template_path = $this->getTemplatePath();
            }
            $this->_template = $template_path . '/' . $template;
        } else {
            $this->_template = $template;
        }
    }

    /**
     * 
     * @return Mixed
     */
    public function getTemplate() {
        return $this->_template;
    }

    /**
     * 
     * @return MVC
     */
    public function getMvcClass() {
        return $this->_mvc;
    }

    public function getControllerClass() {
        return $this->getMvcClass()->getControllerClass();
    }

    public function srun() {
        $this->setTemplatePath($this->getMvcClass()->getTemplatePath());
    }

    public function out() {
        echo $this->getControllerClass()->getActionName();
    }

    public function getContent() {
        $template = $this->getTemplate(); // il template di default è relativo alla cartella templates/ presente nella docroot. Per usarne un'altra cambiare ovviamente il template path presente nella classe controller
        // il controller ha i suoi metodi per il cambio del path
        // quindi da qui posso cancellare la directory
        if (is_readable($template)) {
            $buffer_send = 'tmp-buffer-pre-output-content';
            $buffer_to_return = 'tmp-buffer-for-return-' . rand(0, 100);

            Buffer::saveBuffer($buffer_send, true); // salvo eventuali echo che si sono fatte
            include $template; // includo il template
            Buffer::saveBuffer($buffer_to_return, true); // mi salvo il buffer cancellando il precedente
            // re-invio il buffer precedentemente salvato per non alterare il comportamento
            echo Buffer::getBuffer($buffer_send);
            $return = Buffer::getBuffer($buffer_to_return);
            return $return;
        } else {
            throw new ViewException('Template non trovato', self::TEMPLATE_NOT_FOUND);
        }
    }

    /**
     * outputContent dovrà inviare il contenuto del template
     * Se template è un file, si limita ad un include
     * Se template è una classe , esegue outputContent della classe che
     * dovrà avere come interfaccia TemplateInterface
     * @throws ViewException
     */
    public function outputContent() {
        echo $this->getContent();
    }

}
