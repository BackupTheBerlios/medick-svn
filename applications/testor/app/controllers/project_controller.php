<?php

/**
 * This class is part of elproject, medick sample application
 * $Id$
 * @package elproject.controllers
 */

class ProjectController extends ApplicationController {

    protected $models = array('project');

    protected $use_layout= 'main';

    /** List all projects */
    public function index() {
        try {
            $this->template->projects= Project::find();
        } catch (RecordNotFoundException $rnfEx) {
            $this->render_text($rnfEx->getMessage());
        }
    }

    /** Show one project */
    public function show() {
        try {
            $this->template->project= Project::find($this->params['id']);
        } catch (ActiveRecordException $rnfEx) {
            $this->redirect_to('index');
        }
    }

}

