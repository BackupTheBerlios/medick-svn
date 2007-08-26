<?php
// $Id$
// This file is part of ActiveRecord5, a Medick (http://medick.locknet.ro) Experiment

abstract class SQLConnection extends Object {

  // @var array known drivers
  public static $__drivers= array('sqlite'=>'SQLite');
  
  // @var resource
  protected $resource; 

  // @var string the database
  protected $database;

  // @var string the last executed query
  protected $lastQuery;

  /**
   * Executes an update
   *
   * @param string the sql string to execute
   *
   * @return int
   */ 
  public function executeUpdate( $sql ) {
    return $this->getUpdateCount( $this->exec( $sql ) );
  }
  
  /**
   * Gets the database
   *
   * @return string string
   */ 
  public function getDatabase() { 
    return $this->database;
  }

  /**
   * Sets the database
   * 
   * @param string database to use
   * @return void
   */ 
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

