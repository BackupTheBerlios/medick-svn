<?php

/**
 * This class is part of medick sample application
 * $Id$
 * @package eltodo.controllers
 */

class ApplicationController extends ActionController {

    public function __common() {
        $this->template->title= $this->params['controller'] . '/' . $this->params['action'];
    }

}

