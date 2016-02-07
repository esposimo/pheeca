<?php

namespace smn\pheeca\kernel\Database;

/**
 * Description of SelectInterface
 *
 * @author Simone Esposito
 */
interface SelectInterface {

    public function select($column, $method = ''); // che se non indicato è implicito che sia ALL, ma può essere anche DISTINCT o altro in base al dbms

    public function from($tables = array()); // lista di tabelle in un array o nel formato normale, o nel formato array per gli alias di tabella

    public function where($expression, $negate = null, $conjunction = null);

//			Per aggiungere una condizione where. Qui è particolare in quanto bisogna capire se per la Statement base bisogna usare i bindparams, dei replace, o li metti così com'è con un semplice escape
//			Però poi il modo in cui viene inserito va messo in un metodo a parte, che potrà essere overridato in base al dbms dalle proprie classi

    public function group($columns);

//			Questo metodo deve raggruppare in base al nome di colonna, che può essere anche nel formato <alias-tabella>.<nome-colonna> , oppure <campo|alias>

    public function having($having, $negate = null, $conjunction = null);

//			Questo serve solo se si usa la group by, ed ha la stessa identica logica della where
//			Questo serve solo se si usa la group by, ed ha la stessa identica logica della where

    public function order($columns);

//			Questo deve ordinare in base alle colonne fornite. Le colonnse vanno fornite in un semplice array nel formato [<alias-tabella>].<nome-colonna> [ASC | DESC]

    public function limit($number);

//			Prende $number righe a partire da $offset $number

    public function offset($number);

    public function combine(SelectInterface $select);
    
    public function join($typeJoin, $joinCondition);
}
