<?php

// $Id$
    
include_once('application/models/news.php');
include_once('mock/MockConfigurator.php');
include_once('logger/Logger.php');

class ValidatorTest extends UnitTestCase {

    /**
     * Constructor Once/TestCase
     * Prequsites for this TestCase to run: Create a sqlite DB
     */
    public function ValidatorTest() {
        @unlink(TMP . 'test.db');
        parent::UnitTestCase();
    }

    private function createDatabase() {
        $query='
            CREATE TABLE news (
                id INTEGER PRIMARY KEY,
                title VARCHAR(100),
                body VARCHAR(150)
            );
        ';
        sqlite_query(sqlite_open(TMP . 'test.db'), $query);
    }

    private function removeDatabase() {
        unlink(TMP.'test.db');
    }
    
    /** set up */
    public function setUp() {
        $this->createDatabase();
        Registry::put($configurator= new MockConfigurator(), '__configurator');
        Registry::put(new Logger($configurator), '__logger');
        ActiveRecord::close_connection();
    }
    
    /** tearDown */
    public function tearDown() {
        Registry::close();
        $this->removeDatabase();
    }

    public function testEmpty() {
        $news= News::find();
        $this->assertTrue($news->count()==0);
        $news= new News();
        $news->title="A new News!";
        $this->assertFalse($news->save());
    }

    public function testUniqueTitle() {
        $news= new News();
        $news->title = 'News';
        $news->body  = 'FooBar';
        $this->assertTrue($news->save());
        $news= new News();
        $news->body= 'Ananas';
        $news->title='News';
        $this->assertFalse($news->save());
    }
    
}

