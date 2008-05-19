<?php

// $Id$

class RunnerError extends Exception { }

class TestRunner extends Object {

  private $folder;

  private $cases;

  private $buffer;

  public function __construct($folder) {
    $this->folder= $folder;
    $this->cases = array();
    $this->buffer= '';
  }

  public function run() {
    $this->load_tests( new RecursiveDirectoryIterator($this->folder) );
    
    shuffle($this->cases);

    foreach( $this->cases as $rclass ) {
      $klass= $rclass->newInstance();
      $rclass->getMethod('setup')->invoke($klass);
      $rmethods= $rclass->getMethods();
      try {
        foreach($rmethods as $method) {
          if($method->isPublic() && preg_match("/^test_/", $method->getName())) {
            try {
              $method->invoke($klass);
            } catch(AssertionError $aErr) {
              echo sprintf("[%s::%s] --> %s\n", $klass->class_name(), $method->getName(), $aErr->getMessage());
            }
          }
        } // foreach
      } catch(ReflectionException $rfEx) {
        echo $rfEx->getMessage() . "\n";
      }
      $rclass->getMethod('tear_down')->invoke($klass);
    } // foreach

  }

  private function load_tests( RecursiveDirectoryIterator $dir ) {
    while($dir->valid()) {
      $current= $dir->current();
      if( $dir->isFile() && preg_match("/(.)Test\.php$/", $current->getFilename(), $matches) ) {
        // XXX: handle errors
        include $current->getPathname();
        $x= explode('.', $current->getFilename());
        $class = $x[0];
        $rclass= new ReflectionClass($class);
        if($rclass->getParentClass()->getName() == 'UnitTest') {
          $this->cases[]= $rclass;
        }
      } elseif( $dir->hasChildren() && preg_match("/^\./", $current->getFilename(), $matches) == 0 ) {
        $this->load_tests( $dir->getChildren() );
      }
      $dir->next();
    }

  }

}

