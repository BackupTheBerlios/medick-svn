<?php

// $Id$
    
include_once('mock/MockObject.php');

class RegistryTest extends UnitTestCase {

    private $registry = NULL;
    private $obj      = NULL;
    
    function setUp() {
        $this->obj = new MockObject();
        $this->registry = Registry::put($this->obj, 'mock');
    }
    
    function tearDown() {
        $this->obj      = NULL;
        $this->registry = NULL;
    }
    
    function testRegistry() {
        $this->assertEqual($this->obj->getClassName(), Registry::get('mock')->getClassName());
    }

}
