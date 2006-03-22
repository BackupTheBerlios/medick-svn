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

    /** afiseaza formularul */
    public function edit() {
        try {
            $this->project= Project::find($this->request->getParameter('project'));
        } catch (RecordNotFoundException $rnfEx) {
            $this->redirect_to('all');
        }
    }

    /** update */
    public function update_name() {
        try {
            $this->project= Project::find($this->request->getParameter('project'));
            $name= $this->project->name;
            $this->project->name= $this->params['name'];
            if ( $this->project->save() === FALSE) {
                // render the old name
                $this->render_text($name);
            } else {
                // reder the new name
                $this->render_text($this->project->name);
            }
        } catch (RecordNotFoundException $rnfEx) {
            $this->logger->warn($rnfEx->getMessage());
            $this->render_text($name);
        }
    }
    
    /** Creates a new Project */
    public function create() {
        $this->project= new Project(isset($this->params['project']) ? $this->params['project'] : array());
        try {
            if ( !$this->project->save() ) {
                $this->logger->debug('Cannot save.');
                return $this->render('add');
            }
            $this->flash('notice', 'Project <i>' . $this->project->name . '</i> added!');
            $this->redirect_to('all');
        } catch (Exception $ex) {
            $this->logger->warn($ex->getMessage());
            $this->render('add');
        }
    }

    public function overview() {
        $this->project= Project::find($this->params['id']);
    }

    /** Removes a project */
    public function delete() {
        $project= Project::find($this->params['id']);
        try {
            $project->delete();
            $this->flash('notice', 'Project ' . $project->name . ' removed!');
        } catch (SQLException $sqlEx) {
            $this->logger->warn($ex->getMessage());
            $this->flash('error', 'Cannot remove ' . $project->name . ', ' . $sqlEx->getMessage());
        }
        $this->redirect_to('all');
    }

    /** prints the for for creating a new project */
    public function add() {
        $this->project= new Project();
    }

    /** List all projects, this is the default Route. */
    public function all() {
        $this->projects= Project::find('all', array('order by'=>'created_at desc'));
        if ($this->projects->count() == 0) {
            $this->projects= array();
        }
    }

}

