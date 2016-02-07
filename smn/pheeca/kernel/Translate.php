<?php
namespace smn\pheeca\kernel;

use smn\pheeca\kernel\Translate\GettextLoader as GettextLoader;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Translate
 *
 * @author Simone
 */

class Translate {

    protected static $_language;
    protected static $_domain;
    protected static $_locale;
    protected static $_data = array();
    
    
    /**
     * Imposta la lingua di destinazione della traduzione
     * @param type $language
     */

    public static function setLanguage($language) {
        self::$_language = $language;
    }

    /**
     * Imposta il dominio
     * @param type $domain
     */
    public static function setDomain($domain) {
        self::$_domain = $domain;
    }

    /**
     * Imposta la root dell'alberatura dei file di traduzione 
     * @param type $locale
     */
    public static function setLocale($locale) {
        self::$_locale = $locale;
    }

    /**
     * Carica il file in base al language, domain, locale
     * 
     * @param type $language
     * @param type $domain
     * @param type $locale
     */
    public static function initialize($language = null, $domain = null, $locale = null) {

        if (is_null($language)) {
            $language = self::$_language;
        }
        
        if (is_null($domain)) {
            $domain = self::$_domain;
        }
        
        if (is_null($locale)) {
            $locale = self::$_locale;
        }
        $file = $locale . '/' . $language . '/LC_MESSAGES/' . $domain . '.mo';
        $merge = array();
        // $_data[$language][$domain]
        if (array_key_exists($language, self::$_data)) {
            if (array_key_exists($domain, self::$_data[$language])) {
                $merge = self::$_data[$language][$domain];
            }
        }

        // creare una proprietà per scegliere il tipo di adapter
        $data = new GettextLoader($file);
        $return = array_merge($merge, $data->getData());
        self::$_data[$language][$domain] = $return;
        
    }

    public static function _getTable($language = null, $domain = null) {
        if (is_null($language)) {
            $language = self::$_language;
        }
        if (is_null($domain)) {
            $domain = self::$_domain;
        }

        if (array_key_exists($language, self::$_data)) {
            if (array_key_exists($domain, self::$_data[$language])) {
                return self::$_data[$language][$domain];
            }
        }
        return false;
    }

    public static function _($string, $language = null, $domain = null) {
        $translate_table = Translate::_getTable($language, $domain);
        if (($translate_table) && (array_key_exists($string, $translate_table))) {
            if (is_array($translate_table[$string])) {
            return $translate_table[$string]['translate-singular'];
            }
            return $translate_table[$string];
        }
        return $string;
    }

    public static function __($string, $n = 1, $language = null, $domain = null) {
        $translate_table = Translate::_getTable($language, $domain);
        $n = abs($n);
        if (($translate_table) && (array_key_exists($string, $translate_table))) {
            if (array_key_exists('translate-plural-' . $n, $translate_table[$string])) {
                // plurale preciso
                return $translate_table[$string]['translate-plural-' . $n];
            } else if (($n == 0) || ($n == 1)) {
                return $translate_table[$string]['translate-singular'];
            }
            return $translate_table[$string]['translate-plural'];
        }
        return $string;
    }

}

