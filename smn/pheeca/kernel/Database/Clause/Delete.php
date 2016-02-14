<?php

namespace smn\pheeca\kernel\Database\Clause;

use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\RunnableClauseInterface;
use \smn\pheeca\kernel\Database\BindableClauseInterface;

/**
 * Description of Insert
 *
 * @author Simone Esposito
 */

/**
 * 
 * 'table' => <table>
 * 'rows' => array(
 *  'column' => <value>
 * )
 * 
 */
class Delete extends Clause implements RunnableClauseInterface {

    protected $_name = 'delete';
    protected $_clause = 'DELETE';
    protected $_bind_params = array();
    protected $_tablename = '';

    public function __construct($values, $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $values,
            'suffix' => $suffix
        ]);
    }

    public function processFields() {
        $values = $this->getData();
        $this->_tablename = $values['table'];

        $this->_fields = $this->_tablename;

        if (array_key_exists('rows', $values)) {
            $params = $values['rows'];
            if (($params instanceof BindableClauseInterface) && ($params instanceof Clause)) {
                $string = $params->toString();
                $this->_fields = sprintf('FROM %s %s', $this->_tablename, $string);
                $this->_bind_params = $params->getBindParams();
            } else {
                $params = $values['rows'];
                $fields = array();
                $bindparams = array();
                $counter = 0;
                foreach ($params as $column => $value) {
                    // $column è il nome della colonna
                    // $value è il valore che assumerà
                    $bindparamname = sprintf(':%s_%s', $column,$counter++);
                    $fields[] = sprintf('%s = %s', $column, $bindparamname);
                    $bindparams[$bindparamname] = $value;
                }
                $this->_bind_params = $bindparams;
                $this->_fields = sprintf(
                        'FROM %s WHERE (%s)', $this->_tablename, implode(', ', $fields)
                );
            }
        }
    }

    public function getBindParams() {
        return $this->_bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
