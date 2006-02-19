<?php

// $Id$
    
include_once('application/models/author.php');
include_once('application/models/book.php');
include_once('mock/MockConfigurator.php');
include_once('logger/Logger.php');

/** Test Has_One Association */
class HasOneTest extends UnitTestCase {

    /**
     * Constructor Once/TestCase
     * Prequsites for this TestCase to run: Create a sqlite DB with 2 tables and a foreign key.
     */
    public function __construct() {
        @unlink(TMP . 'test.db');
        $tbd= sqlite_open(TMP . 'test.db');
        $query='
            CREATE TABLE AUTHORS (
                id INTEGER PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(150)
            )';
        $ex= sqlite_query($tbd, $query);
        $_query='    
            CREATE TABLE BOOKS (
                id INTEGER PRIMARY KEY,
                title VARCHAR(100),
                author_id INTEGER NOT NULL CONSTRAINT fk_author_id REFERENCES AUTHORS(id) ON DELETE CASCADE
            )';
        $exp= sqlite_query($tbd, $_query);
    }

    /** set up */
    public function setUp() {
        Registry::put($configurator= new MockConfigurator(), '__configurator');
        Registry::put(new Logger($configurator), '__logger');
        ActiveRecord::close_connection();
        
        $author= new Author();
        $author->name= 'Andrei Cristescu';
        $author->email= 'andrei@foocompany.com';
        
        $id= $author->save();
        
        $book = new Book();
        $book->author_id= $id;
        $book->title= 'The End is NEAR!';
        $book->save();
    }
    
    /** tearDown */
    public function tearDown() {
        Registry::close();
    }

    /** */
    public function testHasOne() {
        $books= Book::find();
        foreach($books as $book) {
            $this->assertIsA($book->author, 'Author');
            $this->assertIsA($book, 'Book');
        }
        $this->assertEqual($book->author->name, 'Andrei Cristescu');
        // TBD. This is not supported right now!
        // $book->author->delete();
        // $this->assertEqual(sizeof(Book::find()), 0);
    }
}
