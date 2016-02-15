<?php

namespace smn\pheeca\kernel\Database\Clause\Mysql;

use \smn\pheeca\kernel\Database\Clause;

/**
 * Description of Limit
 *
 * @author Simone Esposito
 */
class Limit extends Clause {

    protected $_name = 'Limit';
    protected $_clause = 'LIMIT';
    
    /**
     * Imposta il numero di righe da visualizzare in una query
     * $number indica da quale riga della query partire, mentre invece $page
     * indica il numero di righe.
     * Se $number = 0 e $page = 5, verranno restituite le prime 5. Se $number = 1
     * e $page = 5, verranno restituite le righe a partire dalla 2 alla 6 (6 compresa).
     * @param Int $number
     * @param Int $page
     * @param Mixed $prefix
     * @param Mixed $suffix
     */
    public function __construct($number = 0, $page = 0, $prefix = '', $suffix = '') {
        $data = array('number' => $number, 'page' => $page);
        parent::__construct(array('prefix' => $prefix, 'data' => $data, 'suffix' => $suffix));
    }
        
    /**
     * Imposta il numero di righe da visualizzare
     * @param type $number
     */
    public function setNumber($number) {
        $data = $this->getData();
        $data['number'] = $number;
        $this->setData($data);
    }
    
    public function getNumber() {
        $data = $this->getData();
        return $data['number'];
    }
    
    /**
     * Imposta la pagina da cui partire
     * @param Int $page
     */
    public function setPage($page) {
        $data = $this->getData();
        $data['page'] = $page;
        $this->setData($data);
    }
    
    public function getPage() {
        $data = $this->getData();
        return $data['page'];
    }
    
    
    public function processFields() {
        $data = $this->getData();
        $number = $data['number'];
        $page = $data['page'];
        $this->_fields = sprintf('%s,%s', $number, $page);
    }
    
}
