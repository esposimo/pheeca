<?php

namespace smn\pheeca\kernel\Database\Clause;

use \smn\pheeca\kernel\Database\Clause;
use \smn\pheeca\kernel\Database\Query;
use \smn\pheeca\kernel\Database\BindableClauseInterface;

/**
 * Description of Select
 *
 * @author Simone Esposito
 */
class From extends Clause implements BindableClauseInterface {

    protected $_name = 'from';
    protected $_clause = 'FROM';
    
    protected $_derivedTableCounter = 0;

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
            if ($tableName instanceof Query) { // aggiustare qui !!
                $fields[] = sprintf('(%s) %s%s', trim($tableName->toString()), 't', ++$this->_derivedTableCounter);
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

    public function getBindParams() {
        $params = array();
        foreach($this->getData() as $clause) {
            if (($clause instanceof Query) || ($clause instanceof BindableClauseInterface)) {
                $params = array_merge($params, $clause->getBindParams());
            }
        }
        return $params;
    }

}
