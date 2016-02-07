<?php

namespace smn\pheeca\kernel\Database\Statement;
use \smn\pheeca\kernel\Database\Statement\StatementInterface;

/**
 *
 * @author Simone Esposito
 */
interface SelectInterface extends StatementInterface {

    //put your code here


    public function select($columns = array(), $keyword_after = '');

    public function from($tables = array());

    public function join($typeJoin, $joinCondition);

    public function leftJoin($whereCondition);

    public function rightJoin($whereCondition);

    public function where($whereCondition = array(), $conjunction = null, $negate = false);

    public function addAnd($whereCondition = array(), $negate = false);

    public function addOr($whereCondition = array(), $negate = false);

    public function groupBy($columns = array());

    public function having($havingCondition = array(), $conjunction = null, $negate = false);

    public function orderBy($columns = array());

    public function limit($rownumber, $offset = 0);
    
    
}
