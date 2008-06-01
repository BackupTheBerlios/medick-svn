<?php

class FooController extends ApplicationController {

  public function bar($arg1, $arg2, $arg3='bar') {
    // Medick::dump( $arg3 );
  }

  public function mur() {
    $this->template->assign('time', time());
  }

}
