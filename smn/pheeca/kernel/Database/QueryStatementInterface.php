<?php
namespace smn\pheeca\kernel\Database;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Simone Esposito
 */
interface QueryStatementInterface {

    public function toString();

    public function __toString();

    public function __construct($array = array(), $name = 'default');

    // poi vedo
}
