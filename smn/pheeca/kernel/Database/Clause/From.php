<?php

namespace smn\pheeca\kernel\Database\Clause;

use \smn\pheeca\kernel\Database\Clause;

/**
 * Description of Select
 *
 * @author Simone Esposito
 */
class From extends Clause {

    protected $_name = 'from';
    protected $_clause = 'FROM';
    

    public function __construct($fields, $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $fields,
            'suffix' => $suffix]
        );
    }

    public function processFields() {
        $fields = array();
        $tables = $this->getData();
        foreach ($tables as $tableAlias => $tableName) {
            if ($tableName instanceof \Query) { // aggiustare qui !!
                $fields[] = sprintf('(%s)', trim($tableName->toString()));
            } else if (is_numeric($tableAlias)) {
                $fields[] = $tableName;
            } else {
                $fields[] = $tableName . ' ' . $tableAlias;
            }
        }
        $this->_fields = implode(', ', $fields);
    }

    public function formatString() {
        $this->_formedString = sprintf('%s %s %s %s', $this->_clause, $this->_prefix, $this->_fields, $this->_suffix);
    }
    
}
