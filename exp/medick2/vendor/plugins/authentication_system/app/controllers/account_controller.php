<?php

// $Id: $

class AccountController extends ApplicationController {

  public function login($username= null, $password= null) {
    if($this->request->is_get()) return;
    if($user= User::authenticate($username, $password)) {
      $this->session->put('user', $user);
      $this->flash('notice', $user->name . ' authenticated');
      return $this->redirect_to('/');
    }
  }

}

