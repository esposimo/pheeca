<?php
namespace smn\pheeca\kernel\Database\Clause;

use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\RunnableClauseInterface;

/**
 * Description of Insert
 *
 * @author Simone Esposito
 */

/**
 * 
 * 'table' => <table>
 * 'values' => array(
 *  'column' => <value>
 * )
 * 
 */
class Insert extends Clause implements RunnableClauseInterface {

    protected $_name = 'insert';
    protected $_clause = 'INSERT';
    protected $_bind_params = array();
    protected $_tablename = '';

    public function __construct($values, $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $values,
            'suffix' => $suffix
        ]);
        $this->processFields();
    }

    public function processFields() {
        $values = $this->getData();
        $this->_tablename = $values['table'];
        $params = (array_key_exists('values', $values)) ? $values['values'] : array();

        $fields = array();
        $bindparams = array();
        foreach ($params as $column => $value) {
            // $column è il nome della colonna
            // $value è il valore che assumerà
            $fields[] = $column;
            $bindparamname = sprintf(':%s', $column);
            $bindparams[$bindparamname] = $value;
        }
        $this->_bind_params = $bindparams;
        $this->_fields = sprintf(
                'INTO %s(%s) VALUES(%s)', $this->_tablename, implode(', ', $fields), implode(', ', array_keys($this->_bind_params)
        ));
    }

    public function getBindParams() {
        return $this->_bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
