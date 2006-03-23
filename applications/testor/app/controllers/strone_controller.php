<?php
/**
 * 
 * @desc: tests ActiveRecord behavior when keeping an object into the session
 * @package testor.controller
 * $Id$
 */ 
class StroneController extends ApplicationController {

  protected $use_layout= 'main';
  
  protected $models= 'strone';
  
  /**
   * @desc: selects a new strone from DB if we don't have an instance in session
   */ 
  public function index() {
      if ($this->session->hasValue('strone')) {
        $this->strone= $this->session->getValue('strone');
        $this->status= 'from session';
      } else {
        $this->strone= Strone::find(1);
        $this->session->putValue('strone', $this->strone);
        $this->status= 'added to session';
      }
  }
  
  /**
   * @desc: removes strone from the session
   */ 
  public function reload() {
    $this->session->removeValue('strone');
    $this->redirect_to('index');
  }
  
  private function doOperation($op) {
    if ($this->session->hasValue('strone')) {
        $this->strone= $this->session->getValue('strone');
        if ($op == 'inc') $this->strone->inc++;
        elseif($op == 'dec') $this->strone->inc--;
        if ($this->strone->save() !== FALSE) {
            $this->flash('notice', $op == 'inc' ? 'Incresed!' : 'Decreased!');
            $this->redirect_to('index');
        }
    } else {
      $this->flash('error', 'No strone in session');
      $this->redirect_to('index');
    }
  }
  
  /**
   * @desc: performs an operation on this strone, in this case, the ``inc" field is increased by 1
   */ 
  public function increase() {
    return $this->doOperation('inc');
  }
  
  /**
   * @desc: performs an operation on this strone, in this case, the ``inc" field is decreased by 1
   */ 
  public function decrease() {
    return $this->doOperation('dec');
  }
  
  
}

