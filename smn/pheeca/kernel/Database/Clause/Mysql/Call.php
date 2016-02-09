<?php

namespace smn\pheeca\kernel\Database\Clause\Mysql;

use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\BindableClauseInterface;
use \smn\pheeca\kernel\Database;

/**
 * Description of Call
 *
 * @author Simone Esposito
 */
class Call extends Clause implements BindableClauseInterface {

    protected $_name = 'call';
    protected $_clause = 'CALL';
    protected $_bind_params = array();

    public function __construct($procedure_name, $bind_parameters = null, $return_parameters = null, $suffix = '', $prefix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $procedure_name,
            'suffix' => $suffix]
        );

        $this->_bind_params = $bind_parameters;
    }

    public function processFields() {
        $parameters = implode(',', $this->_bind_params);
        $this->_fields = sprintf('%s(%s)',$this->getData(), $parameters);
    }
    
    public function getBindParams() {
        return $this->_bind_params;
    }
}
