<?php

namespace smn\pheeca\kernel\MVC;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Simone Esposito
 */
interface RequestInterface {

    /**
     * Restituisce il valore di un parametro presente nella querystring
     * 
     * @param String $name Nome del parametro presente all'interno della 
     * querystring
     * 
     * @return String|Boolean Restituisce il valore del parametro indicato da 
     * $name presente in una url con querystring.
     * Restituisce false se il parametro non esiste.
     */
    public function qget($name);

    /**
     * Restituisce il valore di un parametro $name ricevuto dopo una chiamata
     * con metodo POST
     * 
     * @param String $name Il nome della chiave presente nei dati ricevuti
     * da una chiamata in POST
     * 
     * @return String|boolean Ritorna un valore di una chiave ricevuta in una
     * chiamata post. Se la 
     */
    public function pget($name);

    /**
     * Restituisce il valore di un header
     * 
     * @param String $name Il nome dell'header di cui si vuole sapere il valore
     * 
     * @return String|Boolean Il valore dell'header richiesto in name. 
     * Restituisce false se l'header non esiste
     */

    public function hget($name);

    /**
     * Restituisce il valore di un cookie
     * @param type $name
     * @return boolean
     */
    public function cget($name);

    /**
     * Restituisce true o false se la chiamata è con metodo POST o GET
     * @return Boolean 
     */
    public function isPost();

    /**
     * Restituisce true se il metodo della chiamata HTTP è GET
     * @return Boolean
     */
    public function isGet();

    /**
     * Restituisce true se una richiesta è in formato ajax. Ovviamente l'header sul quale
     * il metodo fa il test è un header presente in tutti i framework JS come jQuery
     * @return Boolean
     */
    public function isAjax();
    
    
    /**
     * Restituisce tutto il path richiesto, senza considerare querystring
     */
    public function getRequest();
    
    
    /**
     * Restituisce il nome host chiamato
     */
    public function getRequestHost();
    
    /**
     * Restituisce la porta sulla quale è stata fatta la chiamata
     */
    public function getPort();
    
    /**
     * Restituisce lo schema della chiamata (http o https)
     */
    public function getScheme();
    
    /**
     * Restituisce true se la chiamata è in https
     * Restituisce false se la chiamata è in http
     */
    public function isSecureConnection();
    
    
    /**
     * Restituisce il protocollo della chiamata, HTTP/1.0 o HTTP/1.1
     */
    public function getHttpProtocol();
    
    
}
