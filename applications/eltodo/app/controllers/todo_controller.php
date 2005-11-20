<?php

/**
 * This class is part of medick sample application
 * $Id$
 * @package eltodo.controllers
 */

class TodoController extends ApplicationController {

    protected $models = array('todo');

    /** List all items */
    public function all() {
        $this->template->todos_done = Todo::find_done();
        $this->template->todos_not_done = Todo::find_not_done();
    }

    /** Process the form */
    public function create() {
        $todo = new Todo();
        $todo->description = $this->params['description'];
        $todo->save();
        $this->redirect_to('all');
    }

    /** Print the form for adding editing the todo */
    public function edit() {
        $this->template->todo = Todo::find($this->params['id']);
    }

    /** Process the edit form */
    public function update() {
        $todo = new Todo(array('id'=>$this->params['id']));
        $todo->description = $this->params['description'];
        $todo->done = $this->params['done'];
        $todo->save();
        $this->redirect_to('all');
    }

    /** removes an item from the DB */
    public function delete() {
        $todo = new Todo(array('id'=>$this->params['id']));
        $todo->delete();
        $this->redirect_to('all');
    }
    
    /** removes an item from the DB */
    public function checkbox() {
        $todo = Todo::find($this->params['id']);
        $todo->done = $todo->done ? 0 : 1;
        $todo->save();
        $this->redirect_to('all');
    }    
    
}
