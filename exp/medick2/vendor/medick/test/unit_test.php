<?php

error_reporting(E_ALL);

// UnitTest Framework

class AssertionError extends Exception {  }

class UnitTest {

  private $__assertions = 0;

  private $__failues    = 0;

  protected function assert( $condition, $expected= 'true', $message= '' ) {
    $this->__assertions++;
    if($condition === false) {
      // $this->__failures++;
      throw new AssertionError( $message === ''? 'Assertion failed expected: '.$expected.', got: false': $message );
    }
  }

  public function assertions() {
    return $this->__assertions;
  }

  public function report() {
    return sprintf( 'Assertions: %d, Failures: %d', $this->__assertions, $this->__failures );
  }

}

class TestRunner {

  public static function run(UnitTest $test) {

    $failures = 0;
    $errors   = 0;
    $tests    = 0;

    $rclass= new ReflectionClass($test);
    $rmethods= $rclass->getMethods();
    
    try {
      foreach($rmethods as $rmethod) {
        if($rmethod->isPublic() && preg_match("/^test_/", $rmethod->getName())) {
          try {
            $tests++;
            $rmethod->invoke($test);
          } catch(AssertionError $aerr) {
            $failures++;
            echo "--> [" . $rclass->getName() . "::" . $rmethod->getName() . "] " . $aerr->getMessage() . "\n";
          }
        }
      }
    } catch(Exception $ex) {
      $errors++;
    }

    echo sprintf("[%s/%d] assertions: %d, failures: %d, errors: %d\n",
      $rclass->getName(), $tests, $test->assertions(), $failures, $errors);

  }

}

class FooTest extends UnitTest {

  public function test_one() {
    $this->assert(false);
    $this->assert(true);
  }

  public function test_two() {
    $this->assert(true);
    $this->assert(true);
  }

}


TestRunner::run( new FooTest() );
