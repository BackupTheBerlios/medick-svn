<?php

// $Id$
    
include_once('dummy/models/author.php');

/** Tests insert, update, save (insert/update), delete. */
class ARBaseBasicsTest extends UnitTestCase {

    /** <tt>save && delete test</tt> */
    public function testSave() {
        $item= new Author();
        $item->name= 'Andrei Cristescu';
        $this->assertEqual($item->save(), $item->id);
        $this->assertEqual($item->delete(), 1);
    }

    /** <tt>save && delete test</tt> */
    public function testDelete() {
        $item = new Author();
        $item->name = 'Andrei Cristescu';
        $item->save();
        $this->assertEqual($item->delete(), 1);
        $this->assertEqual($item->delete(), 0);
    }
    
    /** <tt>insert</tt> */
    public function testInsert() {
        $item = new Author();
        $item->name = 'Andrei Cristescu';
        $this->assertEqual($item->insert(), $item->id);
        $item->delete();
    }
    /** <tt>update test</tt> */
    public function testUpdate() {
        $item = new Author();
        $item->name = 'Andrei Cristescu';
        $this->assertEqual($item->insert(), $item->id);
        $item->email = 'cristescu@yahoo.com';
        $this->assertEqual($item->update(), 1);
        $this->assertEqual($item->delete(), 1);
    }
}

