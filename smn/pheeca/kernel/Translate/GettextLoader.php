<?php
namespace smn\pheeca\kernel\Translate;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Translate_Gettext
 *
 * @author Simone
 */
class GettextLoader {
    /**
     * @see https://www.gnu.org/software/gettext/manual/html_node/MO-Files.html
     */

    /**
     * Tnx to Zend Gettext class
     * 
     */
    protected $_file; // file po
    protected $_fp; // file pointer
    protected $_magicNumber; // primi 4 byte 
    protected $_le; // little endian vero o falso
    protected $_offsetOriginalString;
    protected $_offsetTranslateString;
    protected $_numberOfString;
    protected $_sizeHashingTable;
    protected $_offsetHashingTable;
    protected $_charset = 'UTF-8';
    protected $_translateList = array();

    public function __construct($file) {
        if (!is_readable($file)) {
            return;
        }
        $this->_file = $file;
        $this->_fp = fopen($file, 'rb');

        fseek($this->_fp, 0);
        $magicnumber = fread($this->_fp, 4);


        if ($magicnumber == "\x95\x04\x12\xde") {
            $this->_le = false;
        } else if ($magicnumber == "\xde\x12\x04\x95") {
            $this->_le = true;
        } else {
            echo 'error';
        }

        $numberOfstring = $this->read(4, 8);
        $offsetOriginalTable = $this->read(); // inizia dal 12° byte e ne legge 4
        $offsetTranslateTable = $this->read(); // inizia dal 16° byte e ne legge 4
        $sizeHashingTable = $this->read(); // inizia dal 20° byte e ne legge 4
        $offsetHashingTable = $this->read(); // inizia dal 24° byte e ne legge 4


        $this->_numberOfString = $numberOfstring;
        $this->_offsetOriginalString = $offsetOriginalTable;
        $this->_offsetTranslateString = $offsetTranslateTable;
        $this->_sizeHashingTable = $sizeHashingTable;
        $this->_offsetHashingTable = $offsetHashingTable;


        // mi porto dove iniziano gli offset delle stringhe originali
        fseek($this->_fp, $this->_offsetOriginalString);

        if ($this->_le) {
            // mi prendo 8 byte * $numberOfString (ovvero 8 byte per il numero di stringhe)
            // ovvero 64 bit di dati, 32 per la lunghezza della stringa, 32 per la posizione da cui parte quella stringa quella stringa
            // facendo l'unpack con V o N, in base al little endian
            // e faccio l'unpack per numerodistringhe, così dovrei avere i prmi due byte che sono la lunghezza dell'offset
            // e gli altri due byte la lunghezza della N stringa
            $originalString = unpack('V' . $numberOfstring * 2, fread($this->_fp, 4 * 2 * $numberOfstring));
        } else {
            $originalString = unpack('N' . $numberOfstring * 2, fread($this->_fp, 4 * 2 * $numberOfstring));
        }

        fseek($this->_fp, $this->_offsetTranslateString);

        if ($this->_le) {
            $translateString = unpack('V' . $numberOfstring * 2, fread($this->_fp, 4 * 2 * $numberOfstring));
        } else {
            $translateString = unpack('N' . $numberOfstring * 2, fread($this->_fp, 4 * 2 * $numberOfstring));
        }

        reset($originalString);

        for ($i = 0; $i < $numberOfstring; $i++) {
            $lenghtOriginal = current($originalString);
            $offsetOriginal = next($originalString);
            next($originalString);

            $lenghtTranslate = current($translateString);
            $offsetTranslate = next($translateString);
            next($translateString);

            if ($lenghtOriginal > 0) {
                fseek($this->_fp, $offsetOriginal); // prendo la stringa originale
                $stringOriginal = explode("\0", fread($this->_fp, $lenghtOriginal));

                fseek($this->_fp, $offsetTranslate); // prendo quella tradotta
                $stringTranslate = explode("\0", fread($this->_fp, $lenghtTranslate));


                // se c'è più di un elemento in stringOriginal, allora vuol dire che ci sono singolari e plurali

                if (count($stringOriginal) > 1 && (count($stringTranslate) > 1)) {
                    // è stato impostato il plurale
                    // $stringOriginal
                    // index 0: stringa singolare originale
                    // index 1: stringa plurale originale
                    // $strintTranslate
                    // index 0: stringa singolare tradotta
                    // index 1: stringa plurale tradotta
                    // index 2-N: stringhe in base al numero preciso


                    $data = array(
                        'original-singular' => $stringOriginal[0],
                        'translate-singular' => $stringTranslate[0],
                        'original-plural' => $stringOriginal[1],
                        'translate-plural' => $stringTranslate[1],
                    );
                    unset($stringTranslate[0]);
                    unset($stringTranslate[1]);
                    foreach ($stringTranslate as $number => $_st) {
                        $index = 'translate-plural-' . $number;
                        $data[$index] = $_st;
                    }
                    $stringTables[$stringOriginal[0]] = $data;
                } else {
                    // condizione normale , senza plurarli
                    $stringTables[$stringOriginal[0]] = $stringTranslate[0];
                }


//                $stringTables[md5(strtolower($stringOriginal))] = array('o' => $stringOriginal, 't' => $stringTranslate);
            } else {
                // sono gli header
                fseek($this->_fp, $offsetTranslate);
                $stringTranslate = explode("\n", fread($this->_fp, $lenghtTranslate));

                $pattern = '/^.*charset=(.*)$/';
                $grep = preg_grep($pattern, $stringTranslate);
                $m = array();
                if ((count($grep) == 1) && (preg_match($pattern, current($grep), $m))) {
                    $this->_charset = $m[1];
                }
            }
        }
        $this->_translateList = $stringTables;
//        echo '<pre>';
//        print_r($this->_translateList);
//        echo '</pre>';
    }

    public function read($byte = null, $position = null) {

        if (is_null($byte)) {
            $byte = 4;
        }
        if (!is_null($position)) {
            fseek($this->_fp, $position);
        }
        $r = fread($this->_fp, $byte);
        if ($this->_le) {
            $read = unpack('Vint', $r);
        } else {
            $read = unpack('Nint', $r);
        }
        return $read['int'];
    }

    public function getData() {
        return $this->_translateList;
    }

}