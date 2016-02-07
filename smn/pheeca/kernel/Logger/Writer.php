<?php

namespace smn\pheeca\kernel\Logger;

use \smn\pheeca\kernel\File as File;

class Writer {

    /**
     * Log Level
     * @var Int 
     */
    protected $_level;

    /**
     * Array indicizzato con i nomi dei livelli di log
     * @var type Array
     */
    protected $_levelNames = array(
        self::LOGLEVEL_EMERG => 'EMERG',
        self::LOGLEVEL_ALERT => 'ALERT',
        self::LOGLEVEL_CRIT => 'CRIT',
        self::LOGLEVEL_ERR => 'ERR',
        self::LOGLEVEL_WARN => 'WARN',
        self::LOGLEVEL_NOTICE => 'NOTICE',
        self::LOGLEVEL_INFO => 'INFO',
        self::LOGLEVEL_DEBUG => 'DEBUG');

    const LOGLEVEL_DEBUG = 7;
    const LOGLEVEL_INFO = 6;
    const LOGLEVEL_NOTICE = 5;
    const LOGLEVEL_WARN = 4;
    const LOGLEVEL_ERR = 3;
    const LOGLEVEL_CRIT = 2;
    const LOGLEVEL_ALERT = 1;
    const LOGLEVEL_EMERG = 0;

    /**
     *
     * @var \kernel\FileCore E' la classe File che gestisce la scrittura nel file 
     */
    protected $_resource;

    /**
     * Lista dei pattern sui quali fare un replace
     * @var type Array
     */
    protected $_patterns = array();
    protected $_model = '[%basefile%:%line%, %d%/%m%/%Y%, %H%:%i%:%s%] (%class%%type%%function%) %levelName%(%level%) : %message%';

    /**
     * 
     * @param String|\kernel\File|Resource $file
     * @param Int $level
     */
    public function __construct($file, $level = null, $patterns = array(), $model = null) {
        if (is_string($file)) {
            $file = new File($file, 'a');
        } else if ((is_resource($file) && (get_resource_type($file) == 'stream'))) {
            $file = new File($file, 'a');
        }
        $this->_resource = $file;

        if (is_null($level)) {
            $level = self::LOGLEVEL_INFO;
        }

        if (is_numeric($level)) {
            $this->_level = $level;
        } else {
            if (array_search($level, $this->_levelNames) === false) {
                $this->_level = self::LOGLEVEL_INFO;
            } else {
                $key = array_search($level, $this->_levelNames);
                $this->_level = $key;
            }
        }

        $this->setPattern('levelName', $this->_levelNames[$this->_level]);
        $this->setPattern('level', $this->_level);
        $this->setPattern('timestamp', function() {
            return \time();
        });
        $this->setPattern('d', function() {
            return \date('d');
        });
        $this->setPattern('D', function() {
            return \date('D');
        });
        $this->setPattern('F', function() {
            return \date('F');
        });
        $this->setPattern('m', function() {
            return \date('m');
        });
        $this->setPattern('y', function() {
            return \date('y');
        });
        $this->setPattern('Y', function() {
            return \date('Y');
        });
        $this->setPattern('H', function() {
            return \date('H');
        });
        $this->setPattern('h', function() {
            return \date('h');
        });
        $this->setPattern('i', function() {
            return \date('i');
        });
        $this->setPattern('s', function() {
            return \date('s');
        });
//        $this->setPattern('session',)

        if (version_compare(phpversion(), '5.4.0', 'lt')) {
            /* check se esiste la sessione per versioni di php minori a 5.4.0 */
            /* se la sessione esiste, creo il pattern per %session% */
            $this->setPattern('session', function() {
                return \session_id();
            });
        } else {
            // altimenti uso session_status()
            $this->setPattern('session', function() {
                return \session_id();
            });
        }

        foreach ($patterns as $p => $v) {
            $this->setPattern($p, $v);
        }

        if (!is_null($model)) {
            $this->_model = $model;
        }
    }

    public function emerg($text) {
        $this->log($text, self::LOGLEVEL_EMERG);
    }

    public function alert($text) {
        $this->log($text, self::LOGLEVEL_ALERT);
    }

    public function crit($text) {
        $this->log($text, self::LOGLEVEL_CRIT);
    }

    public function err($text) {
        $this->log($text, self::LOGLEVEL_ERR);
    }

    public function warn($text) {
        $this->log($text, self::LOGLEVEL_WARN);
    }

    public function notice($text) {
        $this->log($text, self::LOGLEVEL_NOTICE);
    }

    public function info($text) {
        $this->log($text, self::LOGLEVEL_INFO);
    }

    public function debug($text) {
        $this->log($text, self::LOGLEVEL_DEBUG);
    }

    public function log($text, $level = null) {
        /* se non passo il livello, do info di default */
        if (is_null($level)) {
            $level = $this->_level;
        }

        /* se passo il livello come testo, cerco il relativo valore intero */
        if (!is_numeric($level)) {
            $level = array_search(strtoupper($level), $this->_levelNames);
        }

        /* se il livello passato/trovate è maggiore di quello impostato, non loggo . 
         * Es. se il writer è a info e voglio scrivere un messaggio di debug
         */
        if ($level > $this->_level) {
            return;
        }

        /* preparo i dati */
        $debug = debug_backtrace();
        $lastStack = array_pop($debug); /* mi serve sapere la funzione o il metodo della classe dove il Logger è chiamato */

        $index = array('function', 'line', 'file', 'class', 'type');
        //$index = array('function', 'line', 'file', 'class', 'object', 'type');
        /* del debug_backtrace() mi prendo solo quello che mi interessa */
        $filterData = array_intersect_key($lastStack, array_flip($index));

        /* setto tutti i pattern con i rispettivi valori */
        foreach ($filterData as $p => $v) {
            $this->setPattern($p, $v);
        }


        /* aggiungo calledby che comprende il nome della funzione o classe richiamata */

        if (array_key_exists('class', $filterData)) {
            $this->setPattern('calledby', $filterData['class'] . $filterData['type'] . $filterData['function'] . '()');
        } else {
            $this->setPattern('calledby', $filterData['function'] . '()');
        }


        /* aggiungo altre info come messaggio di log, basename(file) , etc */
        if (array_key_exists('file', $filterData)) {
            $this->setPattern('basefile', basename($filterData['file']));
        } else {
            $this->setPattern('basefile', 'nofile');
        }
        $this->setPattern('message', $text);

        /* refuso */
        $patterns = $this->_patterns;
        $string = $this->_model;

        array_walk($patterns, function($value, $pattern, &$combined) {
            // se $value è una callback , diventa il valore stesso della callback eseguito
            if ($value instanceof \Closure) {
                $value = call_user_func($value);
            }
            $combined[1] = str_replace('%' . $pattern . '%', $value, $combined[1]); // effettuo il rimpiazzo
        }, array(&$patterns, &$string));


        $this->write($string);
        foreach ($filterData as $p => $v) {
            $this->removePattern($p);
        }
    }

    /**
     * 
     * @param String $pattern
     * @param String|CallBack $value
     */
    public function setPattern($pattern, $value = '') {
        $this->_patterns[$pattern] = $value;
    }

    /**
     * 
     * @param String $pattern
     * @return String|Boolean|Callback
     */
    public function getPattern($pattern) {
        if (array_key_exists($pattern, $this->_patterns)) {
            return $this->_patterns[$pattern];
        }
        return false;
    }

    /**
     * Rimuove un pattern
     * @param String $pattern
     */
    public function removePattern($pattern) {
        if ($this->getPattern($pattern)) {
            unset($this->_patterns[$pattern]);
        }
    }

    /**
     * Configura il model per il formato del logging
     * @param String $model
     */
    public function setModel($model) {
        $this->_model = $model;
    }

    /**
     * Restituisce il model
     * @return String
     */
    public function getModel() {
        return $this->_model;
    }

    private function write($text) {
        if (substr($text, -1) != PHP_EOL) {
            $text .= PHP_EOL;
        }
        $this->_resource->write($text);
    }

}
