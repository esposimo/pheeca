<?php

namespace smn\pheeca\kernel\Database\Clause\Mysql;

use \smn\pheeca\kernel\Database\Clause;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Where
 *
 * @author Simone Esposito
 */
class Having extends Clause {

    const STATEMENT_KEY_BETWEEN = 'BETWEEN';
    const STATEMENT_KEY_IN = 'IN';
    const STATEMENT_KEY_NEGATE = 'NOT';

    protected $_name = 'having';
    protected $_clause = 'HAVING TO';
    protected $_bindParams = array();
    protected $_condition = array();
    protected $_replacement_counter = 0;
    protected $_uniquePrefixBindParams = '';

    public function __construct($condition = array(), $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $condition,
            'suffix' => $suffix]
        );
        //$this->_condition[] = $condition;
        $this->_uniquePrefixBindParams = uniqid();
    }

    public function processFields() {
        $string = '';
        foreach ($this->getData() as $where) {
            if (is_array($where)) {
                $string .= $this->prepareCondition($where);
            } else if ($where instanceof Clause) {
                $string .= $where->toString();
            }
        }
        $this->_fields = trim($string);
        //$this->_data = sprintf('%s', trim($string));
    }

    /**
     * Da aggiustare !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * @param type $cond
     * @return type
     * @throws StatementException
     */
    private function prepareCondition($cond) {
        $string = '';
        $default = [
            'logic' => '=',
            'conjunction' => '',
            'negate' => false
        ];


        /*
         * column
         * value
         * logic
         * conjunction
         * negate
         */


        $condition = (object) array_merge($default, $cond);

        $column = $condition->column;
        $value = $condition->value;
        $logic = $condition->logic;
        $conjunction = $condition->conjunction;
        $negate = $condition->negate;

        if ($negate == true) {
            $negate = self::STATEMENT_KEY_NEGATE;
        }

        $field = $column;
        if (is_array($column)) {
            $field = key($column) . '.' . current($column);
        }

        // costruisco la query

        if ($logic == self::STATEMENT_KEY_BETWEEN) {
            if (!is_array($value)) {
                throw new StatementException(sprintf('E\' necessario indicare due valori se si usa la clausola %s', self::STATEMENT_KEY_BETWEEN));
            }
            $string = sprintf("%s %s %s AND %s", $field, self::STATEMENT_KEY_BETWEEN, $this->addBindParams($field, $value[0]), $this->addBindParams($field, $value[1]));
        } else if ($logic == self::STATEMENT_KEY_IN) {
            if (!is_array($value)) {
                throw new StatementException(sprintf('E\' necessario indicare la lista di valori per la clausola %s', self::STATEMENT_KEY_IN));
            }
            $s = array();
            foreach ($value as $v) {
                $s[] = $this->addBindParams($field, $v);
            }
            $string = sprintf("%s %s (%s)", $field, self::STATEMENT_KEY_IN, implode(',', $s));
        } else {
            $string = sprintf('%s %s %s %s %s ', $negate, $field, $logic, $this->addBindParams($field, $value), $conjunction); // per le logiche che hanno "=", "<", ">", etc 
        }
        return $string;
    }

    public function addBindParams($name, $value) {
        $replace = sprintf('%s_%s', ':' . $name, $this->_replacement_counter++);
        $this->_bindParams[$replace] = $value;
        return $replace;
    }

    public function addConjunction($whereCondition, $conjunction = 'AND', $negate = false) {
        if (!empty($whereCondition)) {
            $data = $this->getData();
            end($data);
            $data[key($data)]['conjunction'] = $conjunction;
            $data[key($data)]['negate'] = $negate;
            reset($data);
            foreach ($whereCondition as $where) {
                array_push($data, $where);
            }
            $this->setData($data);
        }
    }

    public function addAnd($whereCondition = array(), $negate = false) {
        $this->addConjunction($whereCondition, 'AND', $negate);
    }

    public function addOr($whereCondition = array(), $negate = false) {
        $this->addConjunction($whereCondition, 'OR', $negate);
    }

    public function addNotAnd($whereCondition = array()) {
        $this->addAnd($whereCondition, true);
    }

    public function addNotOr($whereCondition = array()) {
        $this->addOr($whereCondition, true);
    }

}
