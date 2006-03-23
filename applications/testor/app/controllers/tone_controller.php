<?php
/**
 * 
 * @desc: Test for: <ul><li>FormHelper::check_box</li><li>uniqueness_of validator</li><li>ActiveRecordHelper::error_message_on</li></ul>
 * @package testor.controller
 * $Id$
 */ 
class ToneController extends ApplicationController {
    
    protected $use_layout= 'main';
    
    protected $models= 'tone';
    
    /** 
     * @desc: it lists all <em>tones</em> 
     */
    public function index() {
        $this->tone= Tone::find();
    }
    
    /** 
     * @desc: prints the form for adding a new <em>tone</em>, it submits to <a href="tone/create">create</a>
     */
    public function add() {
        $this->tone= new Tone();
    }
    
    /** 
     * @desc: creates a new <em>tone</em>
     */
    public function create() {
        $this->tone= new Tone($this->request->getParameter('tone'));
        // status is a check-box
        if (!isset($this->params['tone']['status'])) {
          $this->tone->status= 0;
        } else {
          $this->tone->status=1;
        }
        if ($this->tone->save() === FALSE) {
            $this->render('add');
        } else {
            $this->flash('notice', '<em>' . $this->tone->name . '</em> created');
            $this->redirect_to('index');
        }
    }
    
    /** 
     * @desc: prints the form for editing a <em>tone</em>, it submits to <a href="tone/update">update</a>
     */
    public function edit() {
        try {
            $this->tone= Tone::find($this->request->getParameter('id'));
        } catch (RecordNotFoundException $rnfEx) {
            $this->flash('error', $rnfEx->getMessage());
            $this->redirect_to('index');
        }
    }

    /** 
     * @desc: updates a <em>tone</em>
     */
    public function update() {
        try {
            $this->tone= Tone::find($this->request->getParameter('id'));
            $this->tone->attributes($this->request->getParameter('tone'));
            if (!isset($this->params['tone']['status'])) $this->tone->status=0;
            $this->logger->debug($this->request->getParameter('tone'));
            if ($this->tone->save() === FALSE) {
                $this->render('edit');
            } else {
                $this->flash('notice', $this->tone->name . ' updated');
                $this->redirect_to('index');
            }
        } catch (ActiveRecordException $arEx) {
            $this->logger->warn($arEx->getMessage());   
            $this->flash('error', $arEx->getMessage());
            $this->redirect_to('index');
        }
    }

    /**
     * @desc: removes a <em>tone</em>
     */
    public function delete() {
        try {
            $tone= Tone::find($this->request->getParameter('id'));
            $tone->delete();
            $this->flash('notice', $tone->name . ' succesfully removed');
            $this->redirect_to('index');
        } catch (ActiveRecordException $arEx) {
            $this->flash('error', $arEx->getMessage());
            $this->redirect_to('index');
        }
    }

}

