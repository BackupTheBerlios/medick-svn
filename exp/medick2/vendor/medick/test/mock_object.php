<?php

error_reporting(E_ALL);

// Mocking Framework

//
// Product.expects(:find).with(1).returns(product)
//
// $o= new MockObject('Product');
// $o->expects('find')->with(1)->returns(new Product());
//

class MockObject {

  private $rclass;

  private $klass;

  private $rmethods;

  private $methods;

  public function __construct( $name, $args= null ) {

    $this->rclass = new ReflectionClass($name);
    $this->klass = $this->rclass->newInstance();

    $this->rmethods= $this->rclass->getMethods();

    $this->methods = array();

    foreach($this->rmethods as $method) {
      if($method->isPublic() && !$method->isStatic() && !$method->isConstructor()) {
        $this->methods[$method->getName()] = $method;
      }
    }

  }

  public function __call($method, $args) {

    if(in_array($method, array_keys($this->methods))) {
      return $this->methods[$method]->invoke($this->klass);
    }

  }

}

class Complex {

  public function behave() {
    echo "shit!\n";
  }

}

$complex= new MockObject('Complex');
$complex->behave();

die();

