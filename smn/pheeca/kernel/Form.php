<?php
namespace smn\pheeca\kernel;


use \smn\pheeca\kernel\Variable\Ini;
use \smn\pheeca\kernel\HTMLElement;
use \smn\pheeca\kernel\Form\Exception;
/**
 * Description of Form
 *
 * @author Simone
 */
class Form {

    /**
     * Restituisce lo stato della validazione
     * @var Boolean
     */
    protected $_validate = null;

    /**
     * Nome del form
     * @var String 
     */
    protected $_name;

    /**
     * Contiene gli elementi del form. L'array è multidimensionale, e ogni indice dell'array
     * deve avere il nome dell'elemento presente nel form
     * Ogni Array relativo al nome dell'elemento deve avere
     *  array('name' => array(
     *                  'validators' => array() Array di validator
     *                  'render' => 'stringa dell'elemento'
     * @var Array 
     */
    protected $_elements = array();

    /**
     * Metodo del form , utile sia se si vuole renderizzare , sia per fare un controllo che il form sia stato
     * chiamato col metodo corretto
     * @var String 
     */
    protected $_method;

    /**
     * Url del form, utile se si vuole renderizzare il form, aggiunge il parametro action=$_action
     * @var String 
     */
    protected $_action;

    /**
     * Se è impostato a true, verifica che i dati passati al metodo valid() abbiamo una corrispondenza netta con
     * quelli configurati nel form, quindi se il form ha più elementi, o si tenta di validare più dati
     * di quelli configurati nel form, genera una FormException
     * @var Boolean 
     */
    protected $_strictMode = false;

    /**
     * Passare il nome del form
     * Un array di elementi
     * Un array di opzioni per il form
     * @param String $name Nome del form, obbligatorio
     * @param Array $elements Lista degli elementi del form
     * @param Array $options Lista di opzioni
     */
    public function __construct($name, $elements = array(), $options = array()) {
        if ($name instanceof Ini) {
            
        }
        if (empty($options)) {
            $this->setMethod('POST');
        }
        $this->_elements = $elements;
        $this->_name = $name;
    }

    /**
     * Aggiunge un elemento al form
     * Il nome è il riferimento per il nome che arriva dal client
     * I validatori servono per la validazione dei dati
     * I render sono opzionali, servono solo se si vule usare il Form sia come strumento di validazione
     * che come strumento di render del form stesso.
     * $render deve essere una HTMLElement, o una sua estensione, se ne farò
     * @param String $name
     * @param Array $validators
     * @param String $render
     */
    public function addElement($name, $validators = array(), $render = null) {
        $array = array('validators' => $validators, 'render' => $render);
        $this->_elements[$name] = $array;
    }

    /**
     * Restituise l'elemento $name del form, compresi validatori e render. Restituisce false se l'elemento $name non esiste
     * @param String $name
     * @return Array|Boolean
     */
    public function getElement($name) {
        if (array_key_exists($name, $this->_elements)) {
            return $this->_elements[$name];
        }
        return false;
    }

    /**
     * Rimuove un elemento dal form
     * @param String $name
     */
    public function removeElement($name) {
        if (array_key_exists($name, $this->_elements)) {
            unset($this->_elements[$name]);
        }
    }

    /**
     * Configura il nome del form, che è l'attributo del tag form
     * @param String $name
     */
    public function setName($name) {
        $this->_name = $name;
    }

    /**
     * Restituisce il nome del form, che coincide con l'attributo name del tag form
     * @return String
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Configura l'attributo method del form
     * @param String $method
     */
    public function setMethod($method) {
        $this->_method = $method;
    }

    /**
     * Restituisce il metodo del form
     * @return String
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * Configura l'attributo action del form
     * @param String $action
     */
    public function setAction($action) {
        $this->_action = $action;
    }

    /**
     * Restituisce il valore dell'attributo action del form
     * @return String
     */
    public function getAction() {
        return $this->_action;
    }

    /**
     * Configura $strict
     * @param Boolean $strict
     */
    public function setStrictMode($strict) {
        $this->_strictMode = $strict;
    }

    /**
     * Restituisce $_strictMode
     * @return Boolean
     */
    public function getStrictMode() {
        return $this->_strictMode;
    }

    
    /**
     * Setta $_validate a null, così da poter rifare la validazione con valid()
     * @return self()
     */
    public function revalidate() {
        $this->_validate = null;
        return $this;
    }

    /**
     * Il metodo render richiama il metodo toString() della classe HTMLElementCore
     * Se si vogliono usare dei render custom e non si vuole overridare il metodo render() di questa classe
     * E' necessario che le classi render utilizzate abbiano il metodo toString che restituiscono una stringa
     * cnotenente il codice html dell'elemento
     * @return String
     */
    public function render() {
        $form = new HTMLElement('form');
        $form->addAttribute('name', $this->getName());
        $form->addAttribute('action', $this->getAction());
        $form->addAttribute('method', $this->getMethod());

        foreach ($this->_elements as $name => $element) {
            $render = $element['render'];
            $form->addChild($name, $render);
        }

        return $form->toString();
    }

    public function __toString() {
        return $this->render();
    }

// uso array_intersect_key per prendere solo i dati del form presenti nei dati ricevuti
// no, i dati ricevuti devono essere quelli del form, pochi cazzi
// magari inventarsi uno strictMode, ovvero che se strictMode = true, i dati che arrivano dal form
// devono coincidere
// se strictMode = false, allora anche se arriva roba in più fa nulla
// strictMode = false di default
// basta domani lavoro, m vac a cuccà




    public function valid($data = array()) {
// validare un post devo ciclare i $data e validarli con i validator relativi

        if (!is_null($this->_validate)) {
            return $this->_validate;
        }

        if ($this->getStrictMode()) {
            $formKeys = array_keys($this->_elements);
            $dataKeys = array_keys($data);
            if (count($dataKeys) == count($formKeys)) {
                $diff = array_diff($dataKeys, $formKeys);
                if (!empty($diff)) {
                    $this->_validate = false;
                    throw new Exception(_t('Gli elementi del form non sono gli stessi dei dati da validare'));
                }
            } else {
                $this->_validate = false;
                throw new Exception(_t('Gli elementi del form non sono gli stessi dei dati da validare'));
            }
        }

        $errors = array();
        foreach ($data as $name => $value) {
            $element = $this->getElement($name);
            $validators = $element['validators'];
            foreach ($validators as $validator) {
                // se il valore ricevuto è un array, faccio il valid per ogni valore
                if (is_array($value)) {
                    foreach ($value as $key => $multiValue) {
                        try {
                            $validator->isValid($multiValue);
                        } catch (Exception $ex) {
                            $errors[$name][] = $ex->getMessage();
                        }
                    }
                } else {
                    try {
                        $validator->isValid($value);
                    } catch (Exception $ex) {
                        $errors[$name][] = $ex->getMessage();
                    }
                }
            }
        }
        if (empty($errors)) {
            $this->_validate = true;
            return true;
// nessun errore, è valido
        } else {
            $this->_validate = false;
            return false;
        }
    }

}
