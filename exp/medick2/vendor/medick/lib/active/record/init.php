<?php

// $Id: $

//  ActionController Framework Autoload Definition
function __active_record_autoload($class) {

  // special case
  if($class == 'ActiveRecord') {
    return require 'active/record/Base.php';
  }

  $base= dirname(__FILE__) . DIRECTORY_SEPARATOR;

}

spl_autoload_register('__active_record_autoload');

