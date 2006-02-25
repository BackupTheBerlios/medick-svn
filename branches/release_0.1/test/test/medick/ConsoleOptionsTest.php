<?php

// $Id$
    
include_once('medick/ConsoleOptions.php');

class ConsoleOptionsTest extends UnitTestCase {

    private $args;
    
    function setUp() {
        $this->args=  array('__FOO__', '--controller', 'Invoker');
    }
    
    function tearDown() {
        $this->args= array();
    }
    
    function testScriptName() {
        $c= new ConsoleOptions($this->args);
        $this->assertTrue($c->getScriptName()==$this->args[0]);
    }

    function testSimpleAlias() {
        $c= new ConsoleOptions($this->args);
        $c->alias('controller', '--controller, -c');
        $this->assertTrue($c->has('-c'));
        $this->assertEqual($c->get(), 'Invoker');
    }

    function testMultipleAliases() {
        $this->args[]= '--methods';
        $this->args[]= '"index, add"';
        $c= new ConsoleOptions($this->args);
        $c->alias('controller', '-c, --controller');
        $c->alias('methods', '-m, --methods, method');
        $this->assertTrue($c->has('-m'));
        $this->assertEqual($c->get('-c'), 'Invoker');
    }

    function testNoValueSimple() {
        $args= array('__FOO__', 'force');
        $c= new ConsoleOptions();
        $c->setNoValueFor('force');
        $c->load($args);
        $this->assertTrue($c->has('force'));
    }

    function testNoValueWithAliases() {
        $args= array('__FOO__', '-f');
        $c= new ConsoleOptions();
        $c->setNoValueFor('force', '-f', '--force');
        $c->load($args);
        $c->alias('force', '-f, --force');
        $this->assertTrue($c->has('--force'));
    }

}
