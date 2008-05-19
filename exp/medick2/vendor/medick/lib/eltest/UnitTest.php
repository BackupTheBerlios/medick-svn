<?php

// $Id$

class AssertionError extends Exception {  }

class UnitTest extends Object {

  private $__assertions = 0;

  protected function assert( $condition, $expected= 'true', $message= '' ) {
    $this->__assertions++;
    if($condition === false) {
      throw new AssertionError( $message === ''? 'Assertion failed expected: '.$expected.', got: false': $message );
    }
  }

  public function setup() { }

  public function tear_down() {  }

  public function assertions() {
    return $this->__assertions;
  }

  public function toString() {
    return sprintf( 'Assertions: %d', $this->__assertions );
  }

}


