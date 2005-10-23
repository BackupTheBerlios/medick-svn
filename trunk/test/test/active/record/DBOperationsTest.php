<?php

// $Id$
    
include_once('dummy/models/author.php');
include_once('mock/MockConfigurator.php');
include_once('logger/Logger.php');

/** Tests insert, update, save (insert/update), delete. */
class DBOperationsTest extends UnitTestCase {

    /**
     * Constructor Once/TestCase
     * Prequsites for this TestCase to run: Create a sqlite DB
     */
    public function __construct() {
        if (is_file(TMP . 'test.db')) unlink(TMP . 'test.db');
        $query='
            CREATE TABLE authors (
                id INTEGER PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(150)
            );
        ';
        sqlite_query(sqlite_open('test.db'), $query);
    }

    /** set up */
    public function setUp() {
        Registry::put(new MockConfigurator(), '__configurator');
        Registry::put(new Logger(), '__logger');
        ActiveRecordBase::close();
    }
    
    /** tearDown */
    public function tearDown() {
        Registry::close();
    }

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