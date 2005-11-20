<?php

/**
 * This class is part of medick sample application
 * $Id$
 * @package eltodo.controllers
 */

class ProjectController extends ApplicationController {

    protected $models = array('project');

    protected $use_layout= 'eltodo';

    /** List all projects */
    public function all() {
        try {
            $this->template->projects= Project::find();
        } catch (RecordNotFoundException $rnfEx) {
            $this->template->projects= array();
        }
    }

}
