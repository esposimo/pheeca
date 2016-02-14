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
class Update extends Clause implements RunnableClauseInterface {

    protected $_name = 'update';
    protected $_clause = 'UPDATE';
    protected $_bind_params = array();
    protected $_new_values = array();
    protected $_tablename = '';

    public function __construct($values, $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $values,
            'suffix' => $suffix
        ]);
    }

    public function processFields() {
        // table => tabella
        // columns => array(column => value)
        // rows => WhereCondition | array()
        $values = $this->getData();
        $this->_tablename = $values['table'];
        $columns = $values['columns'];
        $counter = 0;
        $newvalues = array();
        // creo i SET
        foreach ($columns as $column => $value) {
            $bindparamname = sprintf(':%s_%s', $column, $counter++);
            $newvalues[] = sprintf('%s = %s', $column, $bindparamname);
            $this->_bind_params[$bindparamname] = $value;
        }
        
        $sets = implode(', ', $newvalues);
        $conditions = '';
        if (array_key_exists('rows', $values)) {
            $params = $values['rows'];
            if (($params instanceof BindableClauseInterface) && ($params instanceof Clause)) {
                $conditions = $params->toString();
                $this->_bind_params = array_merge($this->_bind_params, $params->getBindParams());
            } else {
                $fields = array();
                $bindparams = array();
                foreach ($params as $column => $value) {
                    // $column è il nome della colonna
                    // $value è il valore che assumerà
                    $bindparamname = sprintf(':%s_%s', $column, $counter++);
                    $fields[] = sprintf('%s = %s', $column, $bindparamname);
                    $bindparams[$bindparamname] = $value;
                }
                $this->_bind_params = array_merge($this->_bind_params, $bindparams);
                $conditions = 'WHERE ' .implode(', ', $fields);
            }
        }
        $string = ($conditions == '') ? sprintf('%s SET %s', $this->_tablename, $sets) : sprintf('%s SET %s %s', $this->_tablename, $sets, $conditions);
        $this->_fields = $string;
        
    }

    public function getBindParams() {
        return $this->_bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
