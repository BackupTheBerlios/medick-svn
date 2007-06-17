<?php

// $Id$
    
include_once('application/models/author.php');
include_once('mock/MockConfigurator.php');
include_once('logger/Logger.php');
  
/** Tests find */
class FindTest extends UnitTestCase {
  
    /** our authors container */
    private $authors= array();
     
    /**
     * Constructor Once/TestCase
     * Prequsites for this TestCase to run: Create a sqlite DB
     */
    public function __construct() {
        @unlink(TMP . 'test.db');
        $query='
            CREATE TABLE authors (
                id INTEGER PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(150)
            );
        ';
        sqlite_query(sqlite_open(TMP . 'test.db'), $query);
     }
  
     /** set up this test case, we insert 3 fileds in DB table */
     public function setUp() {
        Registry::put($configurator= new MockConfigurator(), '__configurator');
        Registry::put(new Logger($configurator), '__logger');
        ActiveRecord::close_connection();
        $author= new Author();
        $author->name= "Andrei Cristescu";
        $author->email= "andrei.cristescu@foo-factory.info";
        $this->authors[]= $author;
        $author->insert();
        $author= new Author();
        $author->name= "Cristescu";
        $this->authors[]= $author;
        $author->insert();
        $author= new Author();
        $author->name= "Andrei";
        $this->authors[]= $author;
        $author->insert();
    }

    /** remove all the fields from DB, clean-up the Registry */
    public function tearDown() {
        foreach ($this->authors as $author) {
            $author->delete();
        }
        Registry::close();
    }

    /** find all syntax. */
    public function testFindAll() {
        $this->assertEqual(Author::find()->count(), Author::find('all')->count());
    }

    /** id field is not selected, should be NULL */
    public function testFindAllArrayInclude() {
        $authors= Author::find('all', array('columns'=>'name, email'));
        foreach ($authors as $author) {
            $this->assertNull($author->id);
        }
    }

    /** select by condition */
    public function testFindAllArrayCondition() {
        $authors= Author::find('all', array('condition'=>'name=?'), array("Andrei Cristescu"));
        $this->assertEqual($authors->count(), 1);
        foreach ($authors as $author) {
            $this->assertEqual('andrei.cristescu@foo-factory.info', $author->email);
        }
    }

    /** limit syntax. */
    public function testFindAllArrayLimit() {
        $authors= Author::find('all', array('limit'=>2));
        $this->assertEqual($authors->count(), 2);
    }

    /** order syntax. */
    public function testFindAllArrayOrder() {
        $authors= Author::find('all', array('order by'=>'id desc'));
        $i=4; foreach ($authors as $author) {
            $this->assertEqual(--$i, $author->id);
        }
    }

    /** offset syntax. */
    public function testFindAllArrayOffset() {
        $authors= Author::find('all', array('offset'=>2));
        $this->assertEqual($authors->count(), 1);
    }
}

