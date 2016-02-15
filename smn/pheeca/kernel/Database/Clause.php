<?php

namespace smn\pheeca\kernel\Database;

use \smn\pheeca\kernel\Database\ClauseInterface;

class Clause implements ClauseInterface {

    /**
     * Dati prima essere elaborati. Qui vanno inseriti i dati ricevuti nel 
     * costruttore e quelli che possono essere inseriti con eventuali metodi
     * @var Mixed 
     */
    protected $_data = array();

    /**
     * Nome indicativo della clausola (select, from, etc) 
     * @var String 
     */
    protected $_name;

    /**
     * Nome della clausola. Può contenere anche spazi
     * @var String 
     */
    protected $_clause = '';

    /**
     * Dati della clausola ricevuti in fase di creazione della classe.
     * In questa variabile ci sarà anche il dato già pronto per la composizione
     * della query
     * @var Mixed
     */
    protected $_fields;

    /**
     * Prefisso che sarà inserito dopo il nome della clausola ma prima
     * dei dati
     * @var Mixed
     */
    protected $_prefix;

    /**
     * Suffisso che sarà inserito dopo i dati
     * @var Mixed
     */
    protected $_suffix;

    /**
     * Stringa Costruita con l'insieme di clause, prefix, data, suffix
     * @var String
     */
    protected $_formedString = '';

    /**
     * Istanzia la classe ricevendo un array. L'array deve essere nel formato
     * array('prefix' => '', 'data' => '', 'suffix' => '') dove ogni campo
     * può avere un qualunque valore, a seconda di come la classe della clausola
     * viene implementata.
     * @param Array $data
     */
    public function __construct($data = array()) {
        $this->initData($data);
    }

    public function initData($data = array()) {
        if (array_key_exists('prefix', $data)) {
            $this->setPrefix($data['prefix']);
        }
        if (array_key_exists('suffix', $data)) {
            $this->setSuffix($data['suffix']);
        }
        if (array_key_exists('data', $data)) {
            $this->setData($data['data']);
        }
    }

    /**
     * Imposta il prefisso
     * @param Mixed $prefix
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function setPrefix($prefix) {
        $this->_data['prefix'] = $prefix;
        return $this;
    }

    /**
     * Restituisce il prefisso
     * @return type
     */
    public function getPrefix() {
        return $this->_data['prefix'];
    }

    /**
     * Imposta i dati
     * @param Mixed $data
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function setData($data) {
        $this->_data['data'] = $data;
        return $this;
    }

    /**
     * Restituisce i dati
     * @return Mixed
     */
    public function getData() {
        return $this->_data['data'];
    }

    /**
     * Imposta il suffisso
     * @param Mixed $suffix
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function setSuffix($suffix) {
        $this->_data['suffix'] = $suffix;
        return $this;
    }

    /**
     * Restituisce il suffisso
     * @return type
     */
    public function getSuffix() {
        return $this->_data['suffix'];
    }

    /**
     * Costruisce la stringa finale della clausola. Questo metodo, richiamato
     * dal metodo magico __toString() , esegue prima 4 metodi che sono 
     * processPrefix()
     * processData()
     * processSuffix()
     * formatString()
     * Questi 4 metodi vanno overridati quando si implementa una classe di tipo
     * Clause in modo tale da avere la possibilità di creare la clausola come
     * meglio si crede
     * @return String
     */
    public function toString() {
        $this->processPrefix();
        $this->processFields();
        $this->processSuffix();
        $this->formatString();
        return $this->_formedString;
    }

    /**
     * Magic methods
     * @return Mixed
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * Nella classe astratta questi metodi non eseguono nulla
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function processPrefix() {
        $this->_prefix = $this->_data['prefix'];
        return $this;
    }

    /**
     * Nella classe astratta questi metodi non eseguono nulla
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function processFields() {
        $this->_fields = $this->_data['data'];
        return $this;
    }

    /**
     * Nella classe astratta questi metodi non eseguono nulla
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function processSuffix() {
        $this->_suffix = $this->_data['suffix'];
        return $this;
    }

    /**
     * Imposta il nome della clausola (indicativo)
     * @param String $name
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    /**
     * Restituisce il nome della clausola
     * @return String
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Imposta il nome della clausola (usato per formare la stringa)
     * @param String $name
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function setClauseName($name) {
        $this->_clause = $name;
        return $this;
    }

    /**
     * Restituisce il nome della clausola
     * @return String
     */
    public function getClauseName() {
        return $this->_clause;
    }

    /**
     * Imposta la variabile formedString che è la clausola in formato stringa.
     * Se non overridato, questo metodo concatenerà le 4 proprietà
     * clause
     * prefix
     * data
     * suffix
     * @return \smn\pheeca\kernel\Database\Clause
     */
    public function formatString() {
        // clausola standard nella forma CLAUSOLA + DATI
        $this->_formedString = sprintf('%s %s %s %s', $this->_clause, $this->_prefix, $this->_fields, $this->_suffix);
        return $this;
    }

}
