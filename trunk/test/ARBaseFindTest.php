<?php

// $Id$
    
include_once('dummy/models/author.php');

/** Tests find */
class ARBaseFindTest extends UnitTestCase {

    /** our authors container */
    private $authors= array();

    /** set up this test case, we insert 3 fileds in DB table */
    public function setUp() {
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

    /** remove all the fields from DB */
    public function tearDown() {
        foreach ($this->authors as $author) {
            $author->delete();
        }
    }

    /** find all syntax. */
    public function testFindAll() {
        $this->assertEqual(Author::find()->count(), Author::find('all')->count());
    }

    /** id field is not selected, should be NULL */
    public function testFindAllArrayInclude() {
        $authors= Author::find('all', array('include'=>'name, email'));
        foreach ($authors as $author) {
            $this->assertNull($author->id);
        }
    }

    /** select by condition */
    public function testFindAllArrayCondition() {
        $authors= Author::find('all', array('condition'=>'name="Andrei Cristescu"'));
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
        $authors= Author::find('all', array('order'=>'id DESC'));
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
