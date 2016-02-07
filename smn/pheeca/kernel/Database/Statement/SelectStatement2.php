<?php

namespace smn\pheeca\kernel\Database\Statement;

use smn\pheeca\kernel\Database\Statement\SelectInterface;

class StatementException extends \Exception {
    
}

/**
 * Description of SelectStatement
 *
 * @author Simone Esposito
 */
class SelectStatement implements SelectInterface {

    /**
     * Lista dei campi
     * @var Array 
     */
    protected $_select = array();

    /**
     * Lista delle tabelle
     * @var Array 
     */
    protected $_tables = array();

    /**
     *
     * @var Array 
     */
    protected $_where = array();
    protected $_join = array();
    protected $_limit = array();
    protected $_groupBy = array();
    protected $_having = array();
    protected $_order = array();

    /**
     * Parametri da bindare per PDO
     * @var Array 
     */
    protected $_bindParams = array();
    protected $_replacement_counter = 0;

    const STATEMENT_KEY_IN = 'IN';
    const STATEMENT_KEY_BETWEEN = 'BETWEEN';
    const STATEMENT_KEY_LIKE = 'LIKE';
    const STATEMENT_KEY_SELECT = 'SELECT';
    const STATEMENT_KEY_FROM = 'FROM';
    const STATEMENT_KEY_WHERE = 'WHERE';
    const STATEMENT_KEY_LIMIT = 'LIMIT';
    const STATEMENT_KEY_HAVING_TO = 'HAVING TO';
    const STATEMENT_KEY_GROUP_BY = 'GROUP BY';
    const STATEMENT_KEY_JOIN = 'JOIN';
    const STATEMENT_KEY_LEFT_JOIN = 'LEFT JOIN';
    const STATEMENT_KEY_RIGHT_JOIN = 'RIGHT JOIN';
    const STATEMENT_KEY_FULL_JOIN = 'FULL JOIN';
    const STATEMENT_KEY_NEGATE = 'NOT';
    const STATEMENT_SELECT_DISTINCT = 'DISTINCT';
    const STATEMENT_SELECT_ALL = 'ALL';

    /**
     * Costruisce i campi della select
     * @param String|Array $columns Se columns è vuoto, inserisce un "*" in $_select<br>
     * Oppure può essere nel formato
     * $columns = array(
     *  0 => 'field1',
     *  1 => 'field2',
     *  2 => 'field3',
     * );
     * Per avere una query SELECT field1, field2, field3
     * 
     * $columns = array(
     *  'p' => array(
     *      0 => 'field1',
     *      1 => 'field2'
     *  )
     * );
     * Per avere una query SELECT p.field1, p.field2
     * 
     * $columns = array(
     *      'field1' => 'alias1',
     *      'p' => array(
     *          0 => 'field2',
     *          'field3' => 'alias3',
     *      )
     * );
     * Per avere una query SELECT field1 as alias1, p.field2, p.field3 as alias3
     * 
     * @param $keyword_after String Può essere ALL|DISTINCT , a seconda poi del database
     */
    public function select($columns = array(), $keyword_after = '') {
        if (empty($columns)) {
            $this->_select[] = ($keyword_after == '') ? '*' : $keyword_after . ' *';
        } else {
            $this->_select[] = ($keyword_after == '') ? $this->processFields($columns) : $keyword_after . ' ' . $this->processFields($columns);
        }
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

    /**
     * Costruisce i dati per le tabelle
     * @param Array $tables Può essere una stringa (per una singola tabella) o un array
     * di una o più tabelle. Nel formato Array è possibile utilizzare
     * 
     * $tables = array('table', 'table2')
     * Per avere una query FROM table, table2
     * 
     * $tables = array('table', 't' => 'table2')
     * Per avere una query FROM table, table2 t
     */
    public function from($tables = array()) {
        // empty $tables
        $this->_tables = array();
        foreach ($tables as $tableAlias => $tableName) {
            if (is_numeric($tableAlias)) {
                $this->_tables[] = $tableName;
            } else {
                $this->_tables[] = $tableName . ' ' . $tableAlias;
            }
        }
    }

    public function join($typeJoin, $joinCondition) {
        if (!array_key_exists('table', $joinCondition)) {
            throw new StatementException(sprintf('Non è possibile effettuare una join senza indicare una tabella'));
        }

        $this->_join[] = sprintf('%s JOIN %s', $typeJoin, $joinCondition['table']);
        if (array_key_exists('condition', $joinCondition)) {
            $this->_join[] = 'ON';
            $join = '';
            foreach ($joinCondition['condition'] as $on) {
                $join .= $this->prepareCondition($on);
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

    /**
     * @see SelectStatement->prepareCondition()
     * @param Array $whereCondition E' l'array contenente le informazioni per ogni condizione
     * Gli indici sono<br><br>
     * 'column' => Campo relativo alla colonna sulla quale bisogna effettuare la condizione<br><br>
     * 'value' => Il valore da utilizzare per il confronto<br><br>
     * 'validators' => Array con i validatori (classi Validation)<br><br>
     * 'logic' => Logica da utilizzare (valori possibili sono gli operatori logici classici, IN, BETWEEEN, LIKE)
     * Nel caso in cui si utilizzi IN, nella colonna 'value' è necessario indicare i valori utilizzando un array
     * Nel caso in cui si utilizzi BETWEEN, nella colonna 'value' è necessario indicare i valori utilizzando un array 
     * dove in posizione 0 è presente il valore min, in posizione 1 il valore max<br><br>
     * 'conjunction' => Congiunzione da usare (AND, OR)<br><br>
     * 'negate' => Se la condizione va negate (inserisce un NOT all'inizio della condizione)
     */
    public function where($whereCondition = array(), $conjunction = null, $negate = false) {
        $string = '';
        foreach ($whereCondition as $where) {
            $string .= $this->prepareCondition($where);
        }
        $this->_where[] = '(' . trim($string) . ')';
        if (!is_null($conjunction)) {
            $this->_where[] = $conjunction;
        }
        if ($negate === true) {
            $this->_where[] = 'NOT';
        }
    }

    public function groupBy($columns = array()) {
        if (!empty($columns)) {
            $this->_groupBy[] = $this->processFields($columns);
        }
    }

    public function having($havingCondition = array(), $conjunction = null, $negate = false) {
        $string = '';
        foreach ($havingCondition as $having) {
            $string .= $this->prepareCondition($having);
        }
        $this->_having[] = '(' . trim($string) . ')';
        if (!is_null($conjunction)) {
            $this->_having[] = $conjunction;
        }
        if ($negate === true) {
            $this->_having = 'NOT';
        }
    }

    public function orderBy($columns = array()) {
        $this->_order[] = $this->processFields($columns);
    }

    public function addAnd($whereCondition = array(), $negate = false) {
        $this->_where[] = 'AND';
        if ($negate === true) {
            $this->_where[] = 'NOT';
        }
        $this->where($whereCondition);
    }

    public function addOr($whereCondition = array(), $negate = false) {
        $this->_where[] = 'OR';
        if ($negate === true) {
            $this->_where[] = 'NOT';
        }
        $this->where($whereCondition);
    }

    public function limit($rownumber, $offset = 0) {
        // prende $number record a partire dal record $page
        $this->_limit = array($offset, $rownumber);
    }

    public function toString() {
        
    }

    /**
     * 
     * @param Array $condition E' l'array contenente le informazioni per ogni condizione
     * Gli indici sono
     * 'column' => Campo relativo alla colonna sulla quale bisogna effettuare la condizione
     * 'value' => Il valore da utilizzare per il confronto
     * 'validators' => Lista di validatori (classi Validation)
     * 'logic' => Logica da utilizzare (valori possibili sono gli operatori logici classici, IN, BETWEEEN, LIKE)
     * Nel caso in cui si utilizzi IN, nella colonna 'value' è necessario indicare i valori utilizzando un array
     * Nel caso in cui si utilizzi BETWEEN, nella colonna 'value' è necessario indicare i valori utilizzando un array 
     * dove in posizione 0 è presente il valore min, in posizione 1 il valore max
     * 'type' Il tipo di parametro da bindare, sono le costanti PDO::PARAM_*
     * 'conjunction' => Congiunzione da usare (AND, OR)
     * 'negate' => Se la condizione va negate (inserisce un NOT all'inizio della condizione)
     * @param String $prefixForBind Prefisso da utilizzare per eventuali bind nel caso
     * di query che utilizzano le classi PDO
     * @return String
     */
    private function prepareCondition($cond) {
        $string = '';
        $default = [
            'logic' => '=',
            'conjunction' => '',
            'negate' => false
        ];

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

//        $default = [
//            'validators' => null,
//            'logic' => '=',
//            'conjunction' => 'AND',
//            'negate' => false
//        ];
//
//        $condition = array_merge($condition, $default);
//        $validators = $condition['validators'];
//        $logic = $condition['logic'];
//        $conjunction = $condition['conjunction'];
//        $negate = $condition['negate'];
//
//
////        if (array_key_exists('validators', $condition)) {
////            $validators = $condition['validators'];
////        }
////        if (array_key_exists('logic', $condition)) {
////            $logic = $condition['logic'];
////        }
////        if (array_key_exists('conjunction', $condition)) {
////            $conjunction = $condition['conjunction'];
////        }
////        if (array_key_exists('negate', $condition)) {
////            $negate = ($condition['negate'] == true) ? 'NOT' : '';
////        }
//
//        if (array_key_exists('type', $condition)) {
//            $type = $condition['type'];
//        }
//
//
//        $field = $column;
//
//
//
//
//
//// logic
//        /**
//         * se logic è IN , allora il valore da bindare è presente in un array
//         * se logic è BETWEEN, allora $value è un array di due valori, al primo indice il minimo, al secondo indice il max
//         * se logic è un LIKE, allora $value è una stringa
//         * altrimenti è un valore da bindare ugualmente
//         * 
//         * meglio mettere come nome al bind la sintassi <clausola>_<field> dove clausola è la clausola tipo where, join, etc, e <field> è il nome del campo
//         * valutare anche un indice da aggiungere per trasformarlo in <clausola>_<field>_<number> dove number parte da 0 quando si istanzia la classe ed aumenta sempre, se c'è una del bind non diminuisce
//         * 
//         * valutare ancora se creare una classe che gestisca in autonomia le condizioni e restituisca con un metodo (tipo toString()) la condizione 
//         * 
//         * 
//         */
//        if (is_array($column)) {
//            $field = key($column) . '.' . current($column);
//        }
//        if ($logic == 'IN') {
//            if (!is_array($value)) {
//                $value = array($value);
//            }
//            $i = 0;
//            $bindField = array();
//            foreach ($value as $val) {
//                $i++;
//                $bind = ':' . $prefixForBind . $field . '_' . $i;
//                $bindField[] = $bind;
//                $this->addBindParams($bind, $val, $validators, $type);
//            }
//            $string .= implode(' ', array($negate, $field, $logic, '(' . implode(',', $bindField) . ')', $conjunction, ''));
//        } else if ($logic == 'BETWEEN') {
//
//            $between_1 = ':' . $field . '_1';
//            $between_2 = ':' . $field . '_2';
//
//            $this->addBindParams($between_1, $value[0], $validators[0]);
//            $this->addBindParams($between_2, $value[1], $validators[1]);
//            $string .= implode(' ', array($negate, $field, $logic, $between_1, 'AND', $between_2, $conjunction, ''));
//        } else if ($logic == 'LIKE') {
//            $bind = ':' . $prefixForBind . $field;
//            $this->addBindParams($bind, $value, $validators, $type);
//            $string .= implode(' ', array($negate, $field, $logic, $bind, $conjunction, ''));
//        } else {
//            $bind = ':' . $prefixForBind . $field;
//            $this->addBindParams($bind, $value, $validators, $type);
//            $string .= implode(' ', array($negate, $field, $logic, $bind, $conjunction, ''));
//// bind di $field con $field
//        }
//        return $string;
    }

    public function addBindParams($name, $value) {
        $replace = sprintf('%s_%s', $name, $this->_replacement_counter++);
        $this->_bindParams[$replace] = $value;
        return $replace;
    }

//    public function addBindParams($name, $value, $validators = null, $type = null) {
//        $this->_bindParamsType[$name] = array('value' => $value, 'validators' => $validators, 'type' => $type);
//    }
//
//    public function delBindParams($name) {
//        if (array_key_exists($name, $this->_bindParamsType)) {
//            unset($this->_bindParamsType[$name]);
//        }
//    }
}
