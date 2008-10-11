<?php

// $Id: $

class ActiveRecord extends Object {

  protected static $__context;

  public static function __setup(ContextManager $context) {
    self::$__context= $context;
    $model_paths= array();
    foreach($context->load_paths() as $path) {
      if(is_dir($path.'app/models')) $model_paths[]= $path.'/app/models/';
    }
  }

}

