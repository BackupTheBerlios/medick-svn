<?php

/**
 * This class is part of medick sample application
 * $Id$
 * @package eltodo.controllers
 */

class ProjectController extends ApplicationController {

    /** nedded models */
    protected $models = array('project');
    
    /** layout to use for this controller */
    protected $use_layout= 'eltodo';

    /** Creates a new Project */
    public function create() {
        $project= new Project(array('name'=>$this->params['project_name']));
        try {
            $project->save();
            // $this->flash('Project added!');
        } catch (SQLException $ex) {
            $this->logger->warn($ex->getMessage());
        } catch (DuplicateRecordException $drEx) {
            $this->logger->warn($drEx->getMessage());
            // $this->flash('A project with the same name already exists!');
        }
        $this->redirect_to('all');
        // $this->render_text('done');
    }

    /** Removes a project */
    public function delete() {
        $project= new Project (array('id'=>$this->params['id']));
        try {
            $project->delete();
            // $this->flash('Project removed!');
        } catch (SQLException $sqlEx) {
            $this->logger->warn($ex->getMessage());
        }
        $this->redirect_to('all');
    }


    /** List all projects, this is the default Route. */
    public function all() {
        try {
            $this->template->projects= Project::find();
        } catch (RecordNotFoundException $rnfEx) {
            $this->template->projects= array();
        }
    }

}
