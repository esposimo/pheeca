<?php
namespace smn\pheeca\kernel\Variable;

use \smn\pheeca\kernel\File as File;
use \smn\pheeca\kernel\Variable as Variable;

/**
 * Description of Ini
 *
 * @author Simone
 */
class Ini extends Variable {

    /**
     * Stringa di separatore nei file ini per la struttura ad albero
     * @var String
     */
    protected $_separator = '.';

    /**
     * File da processare
     * @var String 
     */
    protected $_file;

    /**
     * Opzioni per la scansione del file da inviare alla funzione parse_ini_file
     * @var Array 
     */

    /**
     * Processa tutte le sezione 
     * @var Boolean 
     */
    protected $_process_sections = true;

    /**
     * Configura la modalità di scansione
     * @var Int 
     */
    protected $_scanner_mode = INI_SCANNER_NORMAL;
    
    
    /**
     * Processa il file $filename
     * @param String|Filename|\kernel\File $filename
     */
    public function file($filename) {
        
        if (is_array($filename)) {
            $this->process($filename);
        }
        else if ((!is_object($filename)) && (is_readable($filename))) {
            $this->_file = $filename;
            $ini = parse_ini_file($this->_file, $this->_process_sections, $this->_scanner_mode);
            $this->process($ini);
        }
        else if ($filename instanceof File) {
            $this->_file = $filename->getFileName();
            $ini = parse_ini_file($this->_file, $this->_process_sections, $this->_scanner_mode);
            $this->process($ini);
        }
    }

    /**
     * Imposta se processare le sezioni del file ini separatamente
     * @param Boolean $process_sections
     * @return \kernel\Variable\Ini
     */
    public function setProcessSections($process_sections = true) {
        $this->_process_sections = $process_sections;
        return $this;
    }

    /**
     * Configura lo scanner mode per la funzione parse_ini_file
     * I valori ammessi sono INI_SCANNER_NORMAL ed INI_SCANNER_RAW
     * @param Int $scanner_mode
     */
    public function setScannerMode($scanner_mode = INI_SCANNER_NORMAL) {
        $this->_scanner_mode = $scanner_mode;
    }

    /**
     * Processa il file e crea la struttura dati
     * @param Array $data
     */
    public function process($data = array()) {
        foreach ($data as $section => $value) {
            if (is_array($value)) {
                // devo creare un oggetto
                if (strpos($section, $this->_separator) !== false) {
                    // c'è un separatore, creo una nuova classe se c'è, altrimenti non la creo
                    $child = $this->createChild($section, $value);
                    $e = explode($this->_separator, $section, 2);
                    $newSection = $e[0];
                } else {
                    $child = new self();
                    $child->process($value);
                    $newSection = $section;
                }
                $this->$newSection = $child;
            } else {
                // se c'è un punto, creo una nuova classe 
                if (strpos($section, $this->_separator) !== false) {
                    $child = $this->createChild($section, $value);
                    $e = explode($this->_separator, $section, 2);
                    $newSection = $e[0];
                    $this->$newSection = $child;
                }
                // se non c'è un punto, inserisco
                else {
                    $this->$section = $value;
                }
            }
        }
    }

    /**
     * Crea una struttura dati $section con i valori $value
     * @param String $section
     * @param String|Array $value
     * @return \self
     */
    public function createChild($section, $value) {
        $e = explode($this->_separator, $section, 2);
        $newSection = $e[0];
        $dataSend = array($e[1] => $value);
        if ($this->$newSection instanceof self) {
            $child = $this->$newSection;
        } else {
            $child = new self();
        }
        $child->process($dataSend);
        return $child;
    }

    /**
     * Restituisce tutta la struttura dati sottoforma di array
     * @return Array
     */
    public function toArray() {
        $return = array();
        foreach ($this->_data as $key => $value) {
            if ($value instanceof self) {
                $return[$key] = $value->toArray();
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * Configura il separatore da utilizzare per le chiavi del file ini e creare
     * strutture dati padre/figlio 
     * @param String $separator
     */
    public function setSeparator($separator = '.') {
        $this->_separator = $separator;
    }

    /**
     * Restituisce il separatore da utilizzare
     * @return String
     */
    public function getSeparator() {
        return $this->_separator;
    }
    
    /**
     * Salva sul file $file il contenuto della struttura
     * @param String|Filename|\kernel\File $file
     */
    public function write($file) {
        if (!$file instanceof File) {
            $file = new File($file);
        }
        $file->close();
        $file->setMode('w');
        $file->fopen();
        $buffer = '';
        if ($this->_process_sections == true) {
            //ogni indice è una sezione
            foreach($this->toArray() as $section => $data) {
                $buffer .= $this->writeSection($section, $data);
                $buffer .= PHP_EOL;
            }
        }
        else {
            $buffer .= $this->writeSection('section', $this->toArray());
        }
        $file->write($buffer);
    }
    
    public function writeSection($section, $data) {
        $buffer = '[' .$section .']' .PHP_EOL;
        foreach($data as $key => $value) {
            if (is_string($value)) {
                $buffer .= $this->writeKey($key,$value);
            }
            else if (is_array($value)) {
                $buffer .= $this->writeSubSection($key, $value);
            }
        }
        return $buffer;
    }
    
    public function writeSubSection($key, $data) {
        $buffer = '';
        foreach($data as $subKey => $value) {
            if (is_string($value)) {
                $buffer .= $this->writeKey($key .$this->_separator .$subKey, $value);
            }
            else {
                $buffer .= $this->writeSubSection($key .$this->_separator .$subKey, $value);
            }
        }
        return $buffer;
    }
    
    public function writeKey($section, $value) {
        $buffer = $section .'="' .$value .'"' .PHP_EOL;
        return $buffer;
    }
    

}
