<?php

// $Id$
    
include_once('mock/MockCollection.php');
    
class Foo Extends Object {      }

class Bar extends Object {      }

class Baz extends Object {      }

class CollectionTest extends UnitTestCase {

    private $col = NULL;

    function setUp() {
        $this->col = new MockCollection();
    }
    
    function tearDown() {
        $this->col = NULL;
    }
    
    function testAdd() {
        $f= $this->col->add(new Foo());
        $this->assertEqual('Foo', $f->getClassName());
    }
    
    function testEmpty() {
        $this->col->add(new Foo());
        $this->assertFalse($this->col->isEmpty());
    }
    
    function testRemove() {
         $f= new Foo();
         $this->col->add($f);
         $this->col->remove($f);
         $this->assertTrue($this->col->isEmpty());
    }
    
    function testSize() {
        $this->col->add(new Foo());
        $this->col->add(new Foo());
        $this->assertEqual(2, $this->col->size());
        $this->col->add(new Bar());
        $this->assertEqual(3, $this->col->size());
    }

    function testAddAll() {
        // $this->col->add(new Foo());
        // $this->col->add(new Bar());
        // $col= new MockCollection();
        // $col->add(new Bar());
        // $col->add(new Baz());
        // $this->col->addAll($col);
        // $this->assertEqual(4, $this->col->size());
    }

    function testArray() {
        $this->col[] = new Foo();
        $this->col[] = new Bar();
        $this->col[] = new Baz();
         $this->assertEqual($this->col->size(), 3);
    }

    function testOffsetExists() {
        // $this->col[] = new Bar();
        // $this->assertTrue($this->col->offsetExists(0));
   }

   function testContains() {
       $f= new Foo();
       $this->col[] = $f;
       $this->assertTrue($this->col->contains($f)); 
   }

}

