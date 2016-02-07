<?php

namespace smn\pheeca\kernel\MVC;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Simone Esposito
 */
interface ControllerInterface {

    public function run();

    public function setView($viewClass);

    public function getView();
}
