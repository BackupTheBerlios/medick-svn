<?php

// $Id: $
/*
$pattern = "/(.)Test.php$/";
$str = "FooTest.php";
var_dump(preg_match( $pattern, $str, $matches ));
# var_dump($matches);
exit;
*/

define( 'RUNNER_VERSION', '0.1/20080518' );

function usage() {
  return sprintf("Usage: %s [folder]\n\nMedick Unit Test Runner, \$v. %s\nReport bugs to aurelian@locknet.ro\n",
    $_SERVER['argv'][0],
    RUNNER_VERSION
  );
}

function main() {
  if( $_SERVER['argc'] <= 1 || false === is_dir($_SERVER['argv'][1])) {
    echo usage();
    exit(-1);
  } else {
    setup();
    init( $_SERVER['argv'][1]);
  }
}

function setup() {
  error_reporting( E_ALL | E_STRICT | E_RECOVERABLE_ERROR );
  define( 'MEDICK_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
  set_include_path(MEDICK_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
  error_reporting( E_ALL | E_STRICT | E_RECOVERABLE_ERROR );
  require 'medick/Medick.php';
  set_error_handler( array(new ErrorHandler(), 'raiseError') );
  Medick::load_framework('eltest');
}

function init( $folder ) {
  $runner= new TestRunner( $folder );
  $runner->run();
}

main();

