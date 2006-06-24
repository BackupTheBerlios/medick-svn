<?php

// $Id$
include_once('active/record/QueryBuilder.php');
include_once('active/record/SQLCommand.php');
include_once('active/support/Inflector.php');
  
class QueryBuilderTest extends UnitTestCase {
    
    public function testCompile() {
        $query= new QueryBuilder('author', array('all'));
        $command= $query->compile();
        $this->assertIsA($command, 'SQLCommand');
        $this->assertEqual('select * from authors', $command->getQueryString());
    }
    
    public function testClauses() {
        $arguments= array();
        $arguments[]='all';
        $arguments[]= array('condition'=>'state=?','columns'=>'name', 'order by'=>'last_login asc');
        $query= new QueryBuilder('author', $arguments);
        $this->assertEqual('select name from authors where state=? order by last_login asc', $query->compile()->getQueryString());
    }
}

