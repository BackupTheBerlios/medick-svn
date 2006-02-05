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

    public function edit() {
      $project= Project::find($this->params['id']);
      $project->name= $this->request->getParameter('name');
      // try {
      $project->save();
      $this->render_text($project->name);
      // }
    }

    /** Creates a new Project */
    public function create() {
        $this->template->project= new Project(isset($this->params['project']) ? $this->params['project'] : array());
        try {
            if ( !$this->template->project->save() ) {
                $this->logger->debug('Cannot save.');
                return $this->render('add');
            }
            $this->flash('notice', 'Project <i>' . $this->template->project->name . '</i> added!');
            $this->redirect_to('all');
        } catch (Exception $ex) {
            $this->render('add');
            $this->logger->warn($ex->getMessage());
        }
    }

    public function overview() {
        $this->template->project= Project::find($this->params['id']);
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
        $this->template->project= new Project();
    }

    /** List all projects, this is the default Route. */
    public function all() {
        try {
          $this->template->projects= Project::find('all', array('order'=>'created_at DESC'));
        } catch (RecordNotFoundException $rnfEx) {
            $this->template->projects= array();
        }
    }

}
