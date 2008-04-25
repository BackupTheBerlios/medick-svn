<?php

require 'medick/Object.php';
require 'medick/ErrorHandler.php';

class Medick extends Object {

  private static $frameworks = array();

  public static function prepare_application() {
    
    set_error_handler( array(new ErrorHandler(), 'raiseError') );

    Medick::load_framework('context');
    Medick::load_framework('logger');
    Medick::load_framework('plugin');
    Medick::load_framework('action_controller');

    // Medick::load_framework('active_record');

  }

  public static function load_framework( $name ) {
    if(in_array($name, Medick::$frameworks)) return;
    // XXX: check the path
    // require la fisierul init din eg. active/record/init.php
    require str_replace( '_' , DIRECTORY_SEPARATOR, $name ) . DIRECTORY_SEPARATOR . 'init.php';
    Medick::$frameworks[]= $name;
  }

  public static function framework_loaded($name) {
    return isset(Medick::$frameworks[$name]);
  }

  public static function version() {
    return '2.0.5';
  }

  public static function dump($o) {
    echo "<pre>";var_dump($o);echo "</pre>";
    die();
  }

}

