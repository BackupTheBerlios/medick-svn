<?php

//
// $Id$
//

//  elTest, Medick UnitTest Framework

function __eltest_autoload( $class ) {
  $base= dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $file= 'eltest'.DIRECTORY_SEPARATOR.$class.'.php';
  if(is_file( $base . $class . '.php')) {
    return require $file;
  }
}

spl_autoload_register('__eltest_autoload');

