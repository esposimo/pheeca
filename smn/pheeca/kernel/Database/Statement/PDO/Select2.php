<?php

namespace smn\pheeca\kernel\Database\Statement\PDO;

use \smn\pheeca\kernel\Database\Statement\SelectInterface;
use \smn\pheeca\kernel\Database\DatabaseException;
use \smn\pheeca\kernel\Database;
use \smn\pheeca\kernel\Validate\Exception as ValidateException;

/**
 * Questa classe serve a creare una query di tipo select
 *
 * @author Simone Esposito
 */



class Select extends \smn\pheeca\kernel\Database\Statement\SelectStatement {
    
    
    
    
    public function toString() {
        $query = array();

        if (empty($this->_select)) {
            $this->_select[] = '*';
        }

        $query[] = sprintf("%s %s", self::STATEMENT_KEY_SELECT, implode(' ', $this->_select));
        $query[] = sprintf("%s %s", self::STATEMENT_KEY_FROM,   implode(' ', $this->_tables));

        if (!empty($this->_join)) {
            $query[] = implode(' ', $this->_join);
        }

        if (!empty($this->_where)) {
            $query[] = sprintf("%s %s", self::STATEMENT_KEY_WHERE, implode(' ', $this->_where));
        }
        
        
        if (!empty($this->_limit)) {
            $query[] = sprintf("LIMIT %s,%s", $this->_limit[0], $this->_limit[1]);
        }


//        if (!empty($this->_groupBy)) {
//            $query[] = 'GROUP BY ' . implode(' ', $this->_groupBy);
//        }
//
//
//        if (!empty($this->_having)) {
//            $query[] = 'HAVING ' . implode(' ', $this->_having);
//        }
//
//
        if (!empty($this->_order)) {
            $query[] = 'ORDER BY ' . implode(' ', $this->_order);
        }
//
//        if (!empty($this->_limit)) {
//            $query[] = 'LIMIT ' . implode(',', $this->_limit);
//        }

        // select
        // from
        // join
        // where
        // group by
        // having
        // order by 
        return implode(' ', $query);
    }
}




class Select2 {

    protected $_select = array();
    protected $_tables = array();
    protected $_join = array();
    protected $_joinRight = array();
    protected $_where = array();
    protected $_having = array();
    protected $_groupBy = array();
    protected $_orderBy = array();
    protected $_limit = array();
    protected $_bindParamsType = array();

    public function select($columns = array()) {
        if (empty($columns['columns'])) {
            $this->_select[] = '*';
        } else {
            $this->_select[] = $this->processFields($columns['columns']);
        }
    }

    public function from($tables = array()) {
        if (is_array($tables)) {
            foreach ($tables as $tableAlias => $tableName) {
                if (is_numeric($tableAlias)) {
                    $this->_tables[] = $tableName;
                } else {
                    $this->_tables[] = $tableName . ' ' . $tableAlias;
                }
            }
        } else {
            $this->_tables[] = $tables;
        }
    }

    public function join($typeJoin, $joinCondition) {

        if (!array_key_exists('table', $joinCondition)) {
            throw new DatabaseException('Bisogna indicare la tabella per effettuare una inner join');
        }

        $table = $joinCondition['table'];
        if (is_array($table)) {
            reset($table);
            $table = current($table) . ' ' . key($table);
        }

        $this->_join[] = $typeJoin . ' JOIN ' . $table;

        if (array_key_exists('on', $joinCondition)) {
            $this->_join[] = 'ON';
            $join = '';
            foreach ($joinCondition['on'] as $on) {
                $join .= $this->prepareCondition($on, 'inner_join_');
            }
            $this->_join[] = '(' . trim($join) . ')';
        }
    }

    public function leftJoin($whereCondition) {
        $this->join('left', $whereCondition);
    }

    public function rightJoin($whereCondition) {
        $this->join('right', $whereCondition);
    }

    public function where($whereCondition = array()) {
        $string = '';
        $this->prepareCondition($whereCondition);
        
//        foreach ($whereCondition as $where) {
//            $string .= $this->prepareCondition($where);
//        }
        $this->_where[] = '(' . trim($string) . ')';
    }

    public function addAnd($whereCondition = array(), $negate = false) {
        $this->_where[] = ($negate == true) ? 'AND NOT' : 'AND';
        $this->where($whereCondition);
    }

    public function addOr($whereCondition = array(), $negate = false) {
        $this->_where[] = ($negate == true) ? 'OR NOT' : 'OR';
        $this->where($whereCondition);
    }

    public function groupBy($fields = array()) {
        $this->_groupBy[] = $this->processFields($fields);
    }

    public function having($condition = array()) {
        $string = '';
        foreach ($condition as $where) {
            $string .= $this->prepareCondition($where, 'having_');
        }
        $this->_having[] = '(' . trim($string) . ')';
    }

    public function orderBy($fields = array()) {
        $this->_orderBy[] = $this->processFields($fields);
    }

    public function limit($rownumber, $offset = 0) {
        // il primo numero è la pagina
        // il secondo numero è la quantità
        $this->_limit = array($offset, $rownumber);
    }

    private function prepareCondition($condition, $prefixForBind = 'where_') {
        
        $string = '';
        $column = $condition['column'];
        $value = $condition['value'];
        $validators = null;
        $logic = '';
        $conjunction = false;
        $negate = false;
        if (array_key_exists('validators', $condition)) {
            $validators = $condition['validators'];
        }
        if (array_key_exists('logic', $condition)) {
            $logic = $condition['logic'];
        }
        if (array_key_exists('conjunction', $condition)) {
            $conjunction = $condition['conjunction'];
        }
        if (array_key_exists('negate', $condition)) {
            $negate = ($condition['negate'] == true) ? 'NOT' : '';
        }

        $field = $column;
        
        
        
        
        
        
        

        if (is_array($column)) {
            $field = key($column) . '.' . current($column);
        }
        if ($logic == 'IN') {
            if (!is_array($value)) {
                $value = array($value);
            }
            $i = 0;
            $bindField = array();
            foreach ($value as $val) {
                $i++;
                $bind = ':' . $prefixForBind . $field . '_' . $i;
                $bindField[] = $bind;
                $this->addBindParams($bind, $val, $validators);
            }
            $string .= implode(' ', array($negate, $field, $logic, '(' . implode(',', $bindField) . ')', $conjunction, ''));
        } else if ($logic == 'BETWEEN') {

            $between_1 = ':' . $field . '_1';
            $between_2 = ':' . $field . '_2';

            $this->addBindParams($between_1, $value[0], $validators[0]);
            $this->addBindParams($between_2, $value[1], $validators[1]);
            $string .= implode(' ', array($negate, $field, $logic, $between_1, 'AND', $between_2, $conjunction, ''));
        } else if ($logic == 'LIKE') {
            $bind = ':' . $prefixForBind . $field;
            $this->addBindParams($bind, $value, $validators);
            $string .= implode(' ', array($negate, $field, $logic, $bind, $conjunction, ''));
        } else {
            $bind = ':' . $prefixForBind . $field;
            $this->addBindParams($bind, $value, $validators);
            $string .= implode(' ', array($negate, $field, $logic, $bind, $conjunction, ''));
            // bind di $field con $field
        }
        return $string;
    }

    private function processFields($columns) {
        $fields = array();
        foreach ($columns as $columnName => $columnAlias) {
            if (is_array($columnAlias)) {
                $fields[] = $this->processSub($columnName, $columnAlias);
            } else {
                $fields[] = $this->processAlias($columnName, $columnAlias);
            }
        }
        return implode(', ', $fields);
    }

    private function processAlias($columnName, $columnAlias, $prefix = null) {
        if (is_numeric($columnName)) {
            return (is_null($prefix)) ? $columnAlias : $prefix . '.' . $columnAlias;
        }
        return (is_null($prefix)) ? $columnName . ' AS ' . $columnAlias : $prefix . '.' . $columnName . ' AS ' . $columnAlias;
    }

    private function processSub($prefix, $columns) {
        $fields = array();
        foreach ($columns as $columnName => $columnAlias) {
            $fields[] = $this->processAlias($columnName, $columnAlias, $prefix);
        }
        return implode(', ', $fields);
    }

    public function toString() {
        $query = array();

        if (empty($this->_select)) {
            $this->_select[] = '*';
        }

        $query[] = 'SELECT ' . implode(' ', $this->_select);
        $query[] = 'FROM ' . implode(' ', $this->_tables);

        if (!empty($this->_join)) {
            $query[] = implode(' ', $this->_join);
        }

        if (!empty($this->_where)) {
            $query[] = 'WHERE ' . implode(' ', $this->_where);
        }


        if (!empty($this->_groupBy)) {
            $query[] = 'GROUP BY ' . implode(' ', $this->_groupBy);
        }


        if (!empty($this->_having)) {
            $query[] = 'HAVING ' . implode(' ', $this->_having);
        }


        if (!empty($this->_orderBy)) {
            $query[] = 'ORDER BY ' . implode(' ', $this->_orderBy);
        }

        if (!empty($this->_limit)) {
            $query[] = 'LIMIT ' . implode(',', $this->_limit);
        }

        // select
        // from
        // join
        // where
        // group by
        // having
        // order by 
        return implode(' ', $query);
    }

    public function addBindParams($name, $value, $validators = null) {
        $this->_bindParamsType[$name] = array('value' => $value, 'validators' => $validators);
    }

    public function delBindParams($name) {
        if (array_key_exists($name, $this->_bindParamsType)) {
            unset($this->_bindParamsType[$name]);
        }
    }

    /**
     * 
     * @param type $instance
     */
    public function run($instance = 'default') {
        // validate dei dati

        foreach ($this->_bindParamsType as $bindParamName => $bindParamValue) {
            $exceptions = null;
            if (array_key_exists('validators', $bindParamValue)) {
                $text = $bindParamValue['value'];
                $validators = $bindParamValue['validators'];
                if ($validators instanceof \smn\pheeca\kernel\Validate) {
                    $validators = array($validators);
                }
                foreach ($validators as $validator) {
                    try {
                        $validator->isValid($text);
                    } catch (ValidateException $ex) {
                        if ($exceptions == null) {
                            $exceptions = $ex;
                        }
                        $exceptions = new ValidateException($ex->getMessage(), $ex->getCode(), $exceptions);
                    }
                }
            }
        }

        if ($exceptions != null) {
            throw new DatabaseException('Validation error!', 0, $exceptions);
        }

        $resource = Database::getInstance($instance)->getDbInstance();
        $statement = $resource->prepare($this->toString());

        foreach ($this->_bindParamsType as $bindParamName => $bindParamValue) {

            $bindValue = $bindParamValue['value'];
            if (array_key_exists('validators', $bindParamValue)) {
                $validators = $bindParamValue['validators'];
                if ($validators instanceof \smn\pheeca\kernel\Validate) {
                    
                }
                $statement->bindValue($bindParamName, $bindValue);
            } else {
                $statement->bindValue($bindParamName, $bindValue);
            }
        }
        $statement->execute();

        if ($statement->errorCode() == 0) {
            echo '<pre>';
            print_r($statement->fetchAll(\PDO::FETCH_ASSOC));
            echo '</pre>';
        } else {
            $code = $statement->errorInfo()[1];
            throw new DatabaseException(implode('|', $statement->errorInfo()), $code);
        }
    }

}
