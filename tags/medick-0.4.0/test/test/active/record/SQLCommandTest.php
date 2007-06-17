<?php

// $Id$

include_once('active/record/SQLCommand.php');
  
class SQLCommandTest extends UnitTestCase {

    private $command = NULL;
    
    public function setUp() {
        $this->command = SQLCommand::select()->from('news');
    }
    
    public function tearDown() {
        $this->command = NULL;
    }

    public function testSimpleSelectCommand() {
        $this->assertEqual($this->command->getQueryString(),'select * from news');
    }
    
    public function testSimpleConditionCommand() {
        $this->command->where('state=?');
        $this->assertEqual($this->command->getQueryString(),'select * from news where state=?');
    }
    
    public function testCommand() {
        $this->command->columns('author, heading')->where('state=?')->orderBy('created_at desc')->where('title is not null');
        $this->assertEqual(
            $this->command->getQueryString(),'select author, heading from news where state=? and title is not null order by created_at desc');
        
    }    
  
}

