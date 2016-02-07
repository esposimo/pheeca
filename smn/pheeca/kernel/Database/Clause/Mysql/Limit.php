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
    
    public function __construct($number, $page = 0, $prefix = '', $suffix = '') {
        $data = array('number' => $number, 'page' => $page);
        parent::__construct(array('prefix' => $prefix, 'data' => $data, 'suffix' => $suffix));
    }
    
    
    public function processFields() {
        $data = $this->getData();
        $number = $data['number'];
        $page = $data['page'];
        $this->_fields = sprintf('%s,%s', $number, $page);
    }
    
}
