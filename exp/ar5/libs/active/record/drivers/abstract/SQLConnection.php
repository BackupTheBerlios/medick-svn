<?php
// $Id$

abstract class SQLConnection extends Object {

  /** @var array 
   */
  public static $__drivers= array('sqlite'=>'SQLite');

  protected $resource; 
  
  protected $database;
  
  protected $lastQuery;

  // return int
  public function executeUpdate( $sql ) {
    return $this->getUpdateCount( $this->exec( $sql ) );
  }

  public function getDatabase() { 
    return $this->database;
  }
  
  public function setDatabase( $database ) { 
    $this->database=$database;
  }

  public function getResource() { 
    return $this->resource;
  }

  public function getLastQuery() { 
    return $this->lastQuery;
  }

  abstract public function connect( Array $dsn= array() );

  abstract public function close(); 

  // return int
  abstract public function nextId();

  // return string
  abstract protected function getLastErrorMessage();

  // return TableInfo
  abstract public function getTableInfo( $name, $force=false );
  
  // return PreparedStatement
  abstract public function prepare( $sql );
  
  // return ResultSet
  abstract public function execute( $sql );

  // return Resource
  abstract public function exec( $sql );
  
  // return int
  abstract public function getUpdateCount( $rs=null );
  
}



