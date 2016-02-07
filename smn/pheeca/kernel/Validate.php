<?php
namespace smn\pheeca\kernel;



use \smn\pheeca\kernel\Variable\Ini;
use \smn\pheeca\kernel\Variable\Xml;
use \smn\pheeca\kernel\Variable\Json;
use \smn\pheeca\kernel\Validate\Exception as ValidateException;

/**
 * Description of ValidateCore
 * La classe astratta ValidateCore serve per validare un testo.
 * Dispone di un metodo astratto validate() che va utilizzato nelle classi estese da ValidateCore
 * Il metodo si occupa di validare un testo e popolare la variabile $_valid a true o false, a seconda se
 * la validazione è andata a buon fine o meno. Inoltre va impostato anche l'errorCode per ricavare il messaggio d'errore
 * La classe dispone inoltre di una lista di messaggi di errore configurabili, e da una lista di pattern
 * configurabili che consistono in regular expression con rispettivi replace che verranno applicati 
 * nella stringa del messaggio d'errore qualora la validazione sia andata male
 * Esempio, in una classe Validator_MaxLength che estende la ValidatorCore , 
 * se ho il messaggio d'errore "La stringa '%text%' è più lunga di %maxlength% caratteri" e come pattern 
 * '/%text%/' => 'Stringa più grande di 10caratteri'
 * '/%maxlength%'/ => '10'
 * Il messaggio sarà cambiato in "La stringa 'Stringa più grande di 10 caratteri' è più lunga di 10 caratteri'
 *
 * @author Simone
 */
abstract class Validate {

    /**
     * Testo da validare
     * @var Mixed 
     */
    protected $text;

    /**
     * Lista di messaggi di errore. 
     * L'array deve essere nel formato array('errorCode' => 'message');
     * @var Array 
     */
    protected $_messages = array();

    /**
     * Codice d'errore per ricavare il messaggio
     * @var String 
     */
    protected $_errorCode = null;
    
    
    /**
     * Lista dei codici numerici relativi all'errore
     * @var Array 
     */
    protected $_errorsCode = array();

    /**
     * Contiene i replace per le stringhe nei messaggi
     * Gli indici dell'array sono i pattern da sostituire , i rispettivi valori sono le stringhe che rimpiazzano
     * Gli indici sono regular expression , pertanto devono contenere i caratteri / ad inizio e fine
     * @var Array 
     */
    protected $_patterns = array();

    /**
     * Variabile impostata dalle classi estese per impostare 
     * @var Boolean
     */
    protected $_valid = false;

    /**
     * Costruttore della classe. Se $text è indicato, viene impostato
     * Se $messages è indicato, viene impostato con il codice $code
     * Se $mssages è un array, viene copiato in $_messages;
     * Se $text è un Variable, o un Variable_Ini, o un Variable_Xml, o altri tipi di Variable
     * Vengono valuti i seguenti indici
     * options =>
     *      optionsName => OptionsValue
     * Dove optionsName è il nome di una proprietà della classe e OptionsValue è il valore da assegnare
     * Con questi due metodi (dell'interfaccia) posso settare da fuori una qualunque variabile della classe, anche se protetta o privata
     * @param String $text
     * @param String|Array $messages
     * @param String $code
     */
    public function __construct($text = '', $messages = null, $code = null) {
        $this->set($text);
        $this->_patterns = array('/%text%/' => $this->get());
        if (is_array($messages)) {
            $merge = $this->_messages;
            $this->_messages = array($merge, $messages);
        } else if ((!is_array($messages)) && (!is_null($code))) {
            $this->addMessage($code, $messages);
        }
    }

    /**
     * Configura tutto il validatore in base alle opzioni inviate
     * Questo metodo sovrascrive i valori di default di un validatore
     * @param Array $options
     */
    public function setOptions($options) {
        if (($options instanceof Ini) || ($options instanceof Xml) || ($options instanceof Json)) {
            $options = $options->toArray();
        }

        if (array_key_exists('options', $options)) {
            foreach ($options['options'] as $optionName => $optionValue) {
                $this->setPropertyName($optionName, $optionValue);
            }
        }

        if (array_key_exists('messages', $options)) {
            foreach ($options['messages'] as $messageCode => $messageText) {
                $this->addMessage($messageCode, $messageText);
            }
        }

        if (array_key_exists('patterns', $options)) {
            foreach ($options['patterns'] as $patternName => $patternReplace) {
                $this->addPattern('%' . $patternName . '%', $patternReplace);
            }
        }
    }

    /**
     * Aggiunge un nuovo messaggio d'errore $messages con codice $code
     * @param Mixed $code
     * @param String $messages
     */
    public function addMessage($code, $messages) {
        $this->_messages[$code] = $messages;
    }

    /**
     * Rimuove un messaggio in base al codice $code
     * @param Mixed $code
     */
    public function removeMessage($code) {
        if (array_key_exists($code, $this->_messages)) {
            unset($this->_messages[$code]);
        }
    }

    /**
     * Restituisce il messaggio d'errore con codice $code. Se il messaggio non esiste, restituisce false
     * @param type $code
     * @return String|Boolean
     */
    public function getMessage($code) {
        if (array_key_exists($code, $this->_messages)) {
            return $this->_messages[$code];
        }
        return false;
    }

    /**
     * Aggiunge un pattern con relativo replace che saranno utilizzati in fase di validazione negativa per comporre il messaggio
     * @param String $pattern
     * @param String $replace
     */
    public function addPattern($pattern, $replace) {
        // se non inizia con / sicuro non è già completa come regular expression
        if (substr($pattern, 0, 1) != '/') {
            $pattern = '/' . $pattern . '/';
        }
        $this->_patterns[$pattern] = $replace;
    }

    /**
     * Restituisce il replace del pattern $pattern. Se il $pattern non esiste, restituisce false
     * @param String $pattern
     * @return String|boolean
     */
    public function getPattern($pattern) {
        if (substr($pattern, 0, 1) != '/') {
            $pattern = '/' . $pattern . '/';
        }
        if (array_key_exists($pattern, $this->_patterns)) {
            return $this->_patterns[$pattern];
        }
        return false;
    }

    /**
     * Elimina , se esiste, un $pattern dalla lista dei patterns.
     * @param String $pattern
     */
    public function removePattern($pattern) {
        if (substr($pattern, 0, 1) != '/') {
            $pattern = '/' . $pattern . '/';
        }
        if (array_key_exists($pattern, $this->_patterns)) {
            unset($this->_patterns[$pattern]);
        }
    }

    /**
     * Imposta il testo da validare successivamente
     * @param String $text
     */
    public function set($text = '') {
        $this->text = $text;
    }

    /**
     * Restituisce il testo impostato
     * @return String
     */
    public function get() {
        return $this->text;
    }

    /**
     * Valida il testo impostato con set() o con il costruttore
     * Se viene passato $text al metodo validate(), viene prima impostato e poi validato
     * @param String $text
     */
    public function isValid($text = null) {
        if (!is_null($text)) {
            $this->set($text);
        }
        $this->validate();

        if ($this->_valid == true) {
            return true;
        } else {
            $this->_messages = preg_replace(array_keys($this->_patterns), array_values($this->_patterns), $this->_messages); // applica le sostituzioni
            $exception_message = '[' . $this->_errorCode . '] ' . $this->getMessage($this->_errorCode);
            $codeException = $this->getErrorByName($this->_errorCode);
            throw new ValidateException($exception_message, $codeException);
            //$message = preg_replace('/%text%/', $this->_text, $this->_messages[$this->_message_type]);
            //throw new Validator_Exception($message);
        }
    }

    /**
     * Imposta lo stato della validazione. Nel caso in cui $state è false, va indicato anche l'erroCode
     * @param Boolean $state
     * @param Mixed $errorCode
     */
    public function setState($state = true, $errorCode = null) {
        $this->_valid = $state;
        if ($state === false) {
            $this->_errorCode = $errorCode;
        }
    }

    public function setPropertyName($name, $value = '') {
        $this->$name = $value;
    }

    public function getPropertyByName($name) {
        return $this->$name;
    }

    /**
     * Metodo da implementare nelle classi estese che contiene la logica di implementazione
     * Il metodo deve solo occuparsi di impostare la variabile $_valid
     * se $_valid è true, allora il testo è validato
     * se $_valid è false, allora il testo non è validato
     */
    abstract public function validate();
    
    
    
    public function getErrorByName($name) {
        return $this->_errorsCode[$name];
    }
    
    public static function getValidatorByName($name, $options = array()) {
        
        $class = '\smn\pheeca\kernel\Validate\\' .$name;
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstance();
        $instance->setOptions($options);
        return $instance;
    }
    
}
