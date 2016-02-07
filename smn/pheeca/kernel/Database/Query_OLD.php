<?php
namespace smn\pheeca\kernel\Database;

class Query {

    protected $_instance;
    protected $_query = array();
    protected $_select;
    protected $_from = array();
    protected $_where = array();
    protected $_join = array();
    protected $_bindparams = array();

    public function __construct($connection = 'default') {
        $this->_instance = Database::getInstance($connection);
    }

    /**
     * Aggiunge i campi della select.
     * I campi possono essere singoli o con il formato Alias => Name
     * @param type $fields
     */
    public function select($fields = array('*')) {

        $this->_select = array();
        $this->_from = array();
        $this->_where = array();
        $this->_join = array();
        $this->_bindparams = array();


//        $_fields = array();
//        if (is_array($fields)) {
//            foreach ($fields as $fieldAlias => $fieldName) {
//                if (is_numeric($fieldAlias)) {
//                    $_fields[] = $fieldName;
//                } else {
//                    $_fields[] = $fieldAlias . ' AS ' . $fieldName;
//                }
//            }
//        }
//        else {
//            $_fields[] = $fields;
//        }
//        
//        $this->_select = 'SELECT ' .implode(' , ' ,$_fields);
//        return $this;
    }

    /**
     * Aggiunge una tabella alla clausola FROM con i campi $fields. Se il secondo parametro non esiste, viene considerata tutta la tabella
     * @param String|Array $tables può essere una stringa 'table'
     * Può essere un array di tabelle array('t' => 'table');
     * @param String|Array $fields Può essere una stringa '*', oppure 'field1, field2, etc'
     * Può essere un'array del tipo array('alias' => 'field', 'alias2' => 'field2')
     *
     */
    public function from($tables, $fields = array('*')) {
        $alias = null;
        if (is_string($tables)) {
            $this->_from[] = $tables;
        }

        if (is_array($tables)) {
            $alias = key($tables);
            $name = current($tables);
            $this->_from[] = $name . ' ' . $alias;
        }

        if (is_string($fields)) {
            $this->_select[] = ($alias != null) ? $alias . '.' . $fields : $fields;
        }
        if (is_array($fields)) {
            foreach ($fields as $fieldAlias => $fieldName) {
                $f = ($alias != null) ? $alias . '.' . $fieldName : $fieldName;
                if (!is_numeric($fieldAlias)) {
                    $f .= ' AS ' . $fieldAlias;
                }
                $this->_select[] = $f;
            }
        }
    }

    /**
     * 
     * @param String|Array $condition Stringa o Array di stringhe con le condizioni.
     * Es. array('field1 > 10') o array('field1 = ?') o array('field1 = :field1);
     * @param String|Array $values Può essere una stringa o numero. Nel caso in cui la condizione $condition presenti la clausola IN , $values deve essere un array con i valori 
     * @param type $nextCondition Se non si sta aggiungendo la prima condizione, può essere AND (default) oppure OR per collegare le successive condizioni
     */
    public function where($condition, $values = null, $nextCondition = 'AND') {
        $where = (count($this->_where) > 0) ? $nextCondition : '';
        $this->_where[] = $where . ' ' . $condition;
        if (!is_null($values)) {
            if (is_array($values)) {
                $this->_bindparams = $values;
            } else {
                $this->_bindparams[] = $values;
            }
        }
    }
    
    
    /**
     * Inserisce una clausola JOIN 
     * @param type $tables Può essere la singola tabella, o nel formato array('alias' => 'table')
     * @param type $join E' la condizione relativa alla join
     */
    public function join($tables, $join) {
        // ci possono essere + join, left, right, etc
        $alias = '';
        $name = $tables;
        if (is_array($tables)) {
            $name = current($tables);
            $alias = key($tables);
        }
        $j = ($alias != '') ? $name .' ' .$alias : $name;
        $j .= ' ON ' .$join;
        
        $this->_join[] = $j;
    }
    

    private function makeQuery() {
        $query = 'SELECT ' . implode(' , ', $this->_select);
        $query .= ' FROM ' . implode(' , ', $this->_from);
        $query .= (!empty($this->_join))  ? ' INNER JOIN ' .implode(' , ', $this->_join) : '';
        $query .= (!empty($this->_where)) ? ' WHERE ' . implode(' ', $this->_where) : '';

//        $data = array();
//        array_walk_recursive($this->_bindparams, function($e, $i, &$data) {
//            $data[0][] = $e;
//        }, array(&$data));

        return $query;
    }

    public function __toString() {
        return $this->makeQuery();
    }

    public function run() {
        $query = $this->makeQuery();
        echo $query .'<br>';
        return $this->_instance->query($query, $this->_bindparams);
    }

}