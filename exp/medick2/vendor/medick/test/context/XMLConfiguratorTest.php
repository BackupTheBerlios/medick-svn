<?php

class XMLConfiguratorTest extends UnitTest {

  public function setup() {
    Medick::load_framework('context');
  }

  public function test_framework_loaded() {
    $this->assert( class_exists('XMLConfigurator') );
  }

}

