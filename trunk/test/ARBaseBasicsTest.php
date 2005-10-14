<?php

// $Id$
    
include_once('dummy/models/todo.php');

/** Tests insert, update, save (insert/update), delete. */
class ARBaseBasicsTest extends UnitTestCase {

    /** <tt>save && delete test</tt> */
    public function testSave() {
        $item= new Todo();
        $item->description= 'A new Todo';
        $this->assertEqual($item->save(), $item->id);
        $this->assertEqual($item->delete(), 1);
    }

    /** <tt>save && delete test</tt> */
    public function testDelete() {
        $item = new Todo();
        $item->description = 'Brr';
        $item->save();
        $this->assertEqual($item->delete(), 1);
        $this->assertEqual($item->delete(), 0);
    }
    
    /** <tt>insert</tt> */
    public function testInsert() {
        $item = new Todo();
        $item->description = 'Inserting...';
        $this->assertEqual($item->insert(), $item->id);
        $item->delete();
    }
    /** <tt>update test</tt> */
    public function testUpdate() {
        $item = new Todo();
        $item->description = 'Foo...';
        $this->assertEqual($item->insert(), $item->id);
        $item->description = 'Bar...';
        $this->assertEqual($item->update(), 1);
        $this->assertEqual($item->delete(), 1);
    }
}

