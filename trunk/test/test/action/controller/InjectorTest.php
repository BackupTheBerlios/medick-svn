<?php

// $Id$

include_once('logger/Logger.php');
include_once('mock/MockConfigurator.php');
include_once('action/controller/Injector.php');

/** Test Injector */
class InjectorTest extends UnitTestCase {

    /** set up */
    public function setUp() {
        Registry::put(new MockConfigurator(), '__configurator');
        Registry::put(new Logger(), '__logger');
        Registry::put(new Injector(FALSE), '__injector');
    }
    
    /** tearDown */
    public function tearDown() {
        Registry::close();
    }

    /** Inject a non existent model */
    public function testFileNotFound() {
        try {
            Registry::get('__injector')->inject('model', 'non_existent_model.php');
            $this->fail('A FileNotFound Exception should be thrown!');
        } catch (Exception $ex) {
            $this->assertIsA($ex, 'FileNotFoundException');
        }
    }

    /** A model that don`t extends ActiveRecordBase */
    public function testWrongModel() {
        try {
            Registry::get('__injector')->inject('model', 'fakemodelone');
            $this->fail('An InjectorException should be thrown!');
        } catch (Exception $ex) {
            $this->assertIsA($ex, 'InjectorException');
        }
        
    }

    /** A model without find method */
    public function /*testW*/rongModelTwo() {
        try {
            Registry::get('__injector')->inject('model', 'fakemodeltwo');
            $this->fail('An InjectorException should be thrown!');
        } catch (Exception $ex) {
            $this->assertIsA($ex, 'InjectorException');
        }
    }
}
