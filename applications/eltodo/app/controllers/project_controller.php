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

    /*
    public function edit() {
      $project= Project::find($this->params['id']);
      $project->name= $this->request->getParameter('name');
      // try {
      $project->save();
      $this->render_text($project->name);
      // }
    }
*/

  /** afiseaza formularul */
  public function edit() {
    try {
      $this->project= Project::find($this->request->getParameter('project'));
    } catch (RecordNotFoundException $rnfEx) {
        $this->redirect_to('all');
    }
  }

  /** update */
    public function update() {
  
        try {
            $this->project= Project::find($this->request->getParameter('id'));
            if ($this->request->getParameter('name') != NULL) {
                $changed= $this->project->name= $this->params['name'];
            }
            if ($this->request->getParameter('description') != NULL) {
                $changed= $this->project->description= $this->params['description'];
            }
            // ->attributes($this->request->getParameter('project'));
            if ( $this->project->save() === FALSE) {
                // erori! se afiseaza din view cu ActiveRecordHelper::error_messages_for($project)
                return $this->redirect_to('all');
            } else {
                $this->render_text($changed);
            }
        } catch (RecordNotFoundException $rnfEx) {
            $this->logger->warn($rnfEx->getMessage());
            $this->flash('error', $rnfEx->getMessage());
            $this->redirect_to('all');
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

