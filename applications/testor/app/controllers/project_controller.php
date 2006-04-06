<?php
/**
 * This class is part of elproject, medick sample application
 * $Id$
 * @package elproject.controllers
 * @desc: Tests ActiveRecord Associations.<br /><code>Project</code> model definition:<ul><li><code>$belongs_to=portfolio;</code></li><li><code>$has_one='manager'</code></li><li><code>$has_many='milestones'</code></li><li><code>$has_and_belongs_to_many='categories'</code></li></ul>
 */

class ProjectController extends ApplicationController {

    protected $models = array('project');

    protected $use_layout= 'main';

    /**
     * @desc: List all projects
     */
    public function index() {
        $this->template->assign('projects', Project::find());
    }

    /**
     * @desc: Shows a project
     */
    public function show() {
        try {
            $this->template->assign('project', Project::find($this->request->getParameter('id')));
        } catch (ActiveRecordException $rnfEx) {
            $this->flash('error', $rnfEx->getMessage());
            $this->redirect_to('index');
        }
    }

}

