<?php
// $Id$
// This file is part of ActiveRecord5, a Medick (http://medick.locknet.ro) Experiment

error_reporting(E_ALL);

class Object {
  
  public function __toString() {
    return $this->toString();
  }

  public function toString() {
    return $this->getClassName();
  }

  public function getClassName() {
    return get_class($this);
  }

}

class MedickException extends Exception { }

class SQLException extends MedickException {  }

class ActiveRecordException extends MedickException { }

class Error extends MedickException {

  public function __construct($message, $code, $file, $line, $trace) {
    parent::__construct($message);
    $this->code  = $code;
    $this->file  = $file;
    $this->line  = $line;
    $this->trace = $trace;
  }
}

class ErrorHandler extends Object {
  public function ErrorHandler() {  }
  public function raise($errno, $errstr, $errfile, $errline) {
    $errRep = error_reporting();
    if( ($errno & $errRep) != $errno) {
      return;
    }
    $trace = debug_backtrace();
    array_shift($trace);
    throw new Error( $errstr, $errno, $errfile, $errline, $trace );
  }
}

set_error_handler( array(new ErrorHandler(), 'raise') );
