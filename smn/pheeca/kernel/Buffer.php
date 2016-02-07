<?php

namespace smn\pheeca\kernel;

//use \smn\kernel\Interfaces\SetAndGet;

/**
 * catturare il buffer attuale
 * catturare il buffer attuale e pulirlo
 * pulire il buffer e basta
 * 
 */

/**
 * Description of Buffer
 *
 * @author Simone
 */
class Buffer {

    /**
     * Lista dei buffer salvati per nome
     * @var Array 
     */
    protected static $_global_buffers = array();

    /**
     * Restituisce ma non stampa in output un buffer dato il nome $name
     * @param String $name
     */
    public static function getBuffer($name) {
        if (array_key_exists($name, self::$_global_buffers)) {
            return self::$_global_buffers[$name];
        }
        return false;
    }

    /**
     * Salva l'attuale buffer con nome $name. Se $clean è vero, cancella il buffer dopo aver salvato
     * @param String $name
     * @param Boolean $clean
     */
    public static function saveBuffer($name, $clean = false) {
        $buffer = '';
        if ($clean == false) {
            $buffer = ob_get_contents();
        } else {
            $buffer = ob_get_clean();
            ob_start();
        }
        self::$_global_buffers[$name] = $buffer;
    }

    /**
     * Cancella il buffer attuale di PHP
     */
    public static function cleanBuffer() {
        ob_clean();
    }

    /**
     * Cancella tutti i buffer memorizzati
     */
    public static function cleanAllBuffer() {
        self::$_global_buffers = array();
    }

    /**
     * Invia in output il buffer con nome $name
     * @param String $name
     */
    public static function outputBuffer($name) {
        $buffer = ob_get_clean(); // mi prendo il buffer e lo cancello
        if (self::getBuffer($name)) {
            echo self::getBuffer($name); // se esiste il buffer, lo invio
        }
        // dopodichè restarto l'output e subito gli carico il buffer salvato
        ob_start();
        echo $buffer;
    }

    /**
     * Accoda il $buffers_append al $buffer_src. Se $buffers_append è un array di nomi, accoda tutti i buffer uno dopo l'altro
     * scorrendo tutti i nomi di $buffers_append.
     * Se $destionation è una stringa , copia tutto il nuovo buffer in $destionation
     * Se $erase_buffer è true, cancella tutti i buffers accodati. 
     * Se $erase_buffer è true e $destination è indicato, oltre a cancellari i buffers accodati cancella anche $buffer_src
     * @param String $buffer_src
     * @param String | Array $buffers_append
     * @param type $destination
     * @param type $erase_buffer
     */
    public static function mergeBuffer($buffer_src, $buffers_append, $destination = null, $erase_buffer = false) {

        $src = self::$_global_buffers[$buffer_src];
        $dst = '';
        $erase = array();
        if (is_array($buffers_append)) {
            foreach ($buffers_append as $name) {
                $dst .= self::$_global_buffers[$name];
                array_push($erase, $name);
            }
        } else {
            $dst .= self::$_global_buffers[$buffers_append];
            $erase = array($buffers_append);
        }

        self::$_global_buffers[$buffer_src] .= $dst; /* merge eseguito */

        if (!is_null($destination)) {
            // se $destination non è null, allora copio tutto in $destination
            self::$_global_buffers[$destination] = self::$_global_buffers[$buffer_src];
        }

        if ($erase_buffer === true) {
            unset(self::$_global_buffers[$buffer_src]);
            foreach ($erase as $e) {
                unset(self::$_global_buffers[$e]);
            }
        }
    }

    public static function g() {
        //ob_end_clean();
        echo '<pre>';
        print_r(self::$_global_buffers);
        echo '</pre>';
    }

    public static function initialize() {
        ob_start();
    }

    public static function stopOutput() {
        ob_end_clean();
    }

}
