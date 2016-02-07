<?php

namespace smn\pheeca\kernel;

/**
 * Description of File
 *
 * @author Simone
 */
class File {

    /**
     * Percorso del file
     * @var String 
     */
    protected $_file;

    /**
     * Risorsa stream che punta al file
     * @var Resource 
     */
    protected $_resource;

    /**
     * Modalità di default per l'apertura del puntatore al file
     * @var String 
     */
    protected $_mode = 'r';

    /**
     * Istanzia la classe
     * Se è una Stringa , crea un file con nome il valore delal stringa passata
     * Se è una risorsa, la prende in carico
     * Se $file è il percorso di un file che non esiste, bisogna 
     * rinominare il file
     * fare una fclose()
     * impostare il mode a 'w' in modo tale da svuotarlo
     * fare una fopen()
     * @param String|Resource|Filename $file
     * @param String $mode
     */
    public function __construct($file, $mode = 'r') {
        $this->_mode = $mode;
        if ((is_int($file) || (is_float($file)))) {
            throw new \Exception(sprintf(_t('Non puoi usare un valore interno come nome file: hai usato ' . $file)));
        }

        if ((is_resource($file)) && (get_resource_type($file) == 'stream')) {
            // è uno stream
            $this->_resource = $file;
            $streaminfo = stream_get_meta_data($file);
            $this->_file = $streaminfo['uri'];
        } else if (is_string($file)) {
            // può essere un file
            if (!is_readable($file)) {
                if (file_exists($file)) {
                    throw new \Exception(sprintf(_t('Non hai i permessi sul file %s indicato'), $file));
                } else {
                    // non è leggibile e non esiste, quindi si suppone che stia provando a crearlo
                    if (str_replace('+', '', $this->getMode()) == 'r') {
                        throw new \Exception(sprintf(_t('Non puoi aprire un file in lettura se non esiste')));
                    }
                    $this->_file = $file;
                    $this->fopen();
                }
            } else {
                $this->_file = $file;
                $this->fopen();
            }
        }
    }
    
    
    public function getResource() {
        return $this->_resource;
    }

    public function fopen() {
        $fp = @fopen($this->_file, $this->_mode);
        if (!$fp) {
            throw new \Exception(sprintf(_t('Impossibile aprire il file ' . $this->_file . ' con modalita ' . $this->_mode)));
        }
        $this->_resource = $fp;
    }

    /**
     * Restituisce il percorso del fle
     * @return String
     */
    public function getFileName() {
        if ((is_resource($this->_resource)) && (get_resource_type($this->_resource) == 'stream')) {
            $streaminfo = stream_get_meta_data($this->_resource);
            return $streaminfo['uri'];
        }
        return $this->_file;
    }

    /**
     * Fa una rename o una move del file
     * @param String $file
     * @param String $newdirectory
     */
    public function rename($file, $newdirectory = null) {
        if (is_null($newdirectory)) {
            $newdirectory = dirname($this->_file);
        }
        $this->close();
        rename($this->_file, $newdirectory . '/' . $file);
        $this->setMode('a+');
        $this->_file = $newdirectory . $file;
        $this->fopen();
    }

    public function delete() {
        $filename = $this->getFileName();
        $this->close();
        if (!unlink($filename)) {
            throw new \Exception(sprintf(_t('Impossibile cancellare il file %s'), $filename));
        }
        $this->_resource = null;
        $this->_file = null;
    }

    /**
     * Fa una copia del file
     * @param String $file
     * @param String $newdirectory
     */
    public function copy($file, $newdirectory = null) {
        $olddirectory = dirname($this->_file);
        if (is_null($newdirectory)) {
            $newdirectory = $olddirectory;
        }
        copy($this->_file, $newdirectory . $file);
    }

    /**
     * Imposta la modalità di lettura del file
     * @param String $mode
     */
    public function setMode($mode = 'r') {
        $this->_mode = $mode;
    }

    /**
     * Restituisce la modalità di lettura del file
     * @return String
     */
    public function getMode() {
        return $this->_mode;
    }

    /**
     * Chiude il puntatore a file
     */
    public function close() {
        fclose($this->_resource);
    }

    /**
     * Scrive i dati nel file
     * @param Mixed $data
     */
    public function write($data) {
        if (fwrite($this->_resource, $data) === false) {
            return false;
        }
        return true;
    }

    public static function getFilesFromDirectory($dirname, $extension = '*', $recursive = true) {
        if (is_array($extension)) {
            $regex = '/^.*\.(' . implode('|', $extension) . ')$/';
        } else {
            if ($extension == '*') {
                $regex = '/^(.*)$/';
            } else {
                $regex = '/^(.*)\.' . $extension . '$/';
            }
        }
        $listOfFiles = array();

        foreach (preg_grep('/^\.\.|\.$/', scandir($dirname), PREG_GREP_INVERT) as $f) {
            $newfile = $dirname . '/' . $f;
            if (is_dir($newfile)) {
                if ($recursive === true) {
                    $listOfFiles = array_merge($listOfFiles, self::getFilesFromDirectory($newfile, $extension, $recursive));
                }
            } else {
                if (preg_match($regex, $newfile)) {
                    array_push($listOfFiles, $newfile);
                }
            }
        }
        return $listOfFiles;
    }

    public static function getFileUnixFormat($filename) {
        return preg_replace('/\x5c/', '/', $filename);
    }

    public static function getFileWindowsFormat($filename) {
        return preg_replace('/\x2f/', '\\', $filename);
    }

    public static function getFileFormatByOs($filename) {
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            return File::getFileWindowsFormat($filename);
        } else {
            return File::getFileUnixFormat($filename);
        }
    }

    public static function normalizePathOfFile($file) {
        $temp = preg_replace('/\/\//', '/', preg_replace('/\x5c/', '/', $file));
        while (str_replace('//', '/', $temp) != $temp) {
            $temp = str_replace('//', '/', $temp);
        }
        return $temp;
    }

    /**
     * Crea un file ed inserisce il contenuto al suo interno
     * @param type $text
     * @param type $filename
     * @return \self
     */
    public static function createFileFromText($text, $filename = null) {
        if (is_null($filename)) {
            $filename = tmpfile();
        }
        $self = new self($filename, 'w+');
        $self->write($text);
        return $self;
    }
    
    
    public static function getFileNameFromInstance($instance) {
        
        if ($instance instanceof File) {
            return $instance->getFileName();
        }
        
        else if ((is_resource($instance)) && (get_resource_type($instance) == 'stream')) {
            // è uno stream
            $streaminfo = stream_get_meta_data($instance);
            return $streaminfo['uri'];
        }
        else if ((is_string($instance)) && (file_exists($instance))) {
            return $instance;
        }
        else {
            return false;
        }
    }
    
    public static function getTemporanyFile($mode = 'w') {
        return new self(tmpfile(), $mode);
    }

}
