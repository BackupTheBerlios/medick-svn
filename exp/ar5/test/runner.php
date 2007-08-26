<?php
// $Id: $

set_include_path('../libs:libs');

include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');

include_once('../ar5_base.php');

include_once('mock/configurator.php');
include_once('mock/driver.php');

// include_once('context/configurator/XMLConfigurator.php');

include_once('active/record/drivers/sqlite/sqlite.php');

include_once('active/record/Base.php');

$config= new MockConfigurator();

class TestSetup extends UnitTestCase {
  
  function testTrue() {
    
  }
  
}

$test = new TestSetup();
$test->run(new TextReporter());
