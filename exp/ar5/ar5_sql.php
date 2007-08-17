<?php
// $Id$

class SQLException extends MedickException {  }

class Field extends Object {

  private $name, $value, $pk, $type;

  public function Field($name, $pk=0, $type='int', $value=null) {
    $this->name= $name;
    $this->value=$value;
    $this->pk= $pk;
    $this->type=$type;
  }

  public function getName() { return $this->name; }
  public function getValue() { return $this->value; }
  public function isPk() { return (bool)$this->pk; }
  public function getType() { return $this->type; }

}

abstract class SQLConnection extends Object {

  protected $resource, $database, $lastQuery;

  public function getDatabase() { return $this->database; }
  public function setDatabase( $database ) { $this->database=$database;}
  public function getResource() { return $this->resource; }
  public function setResource( $resource ) { $this->resource= $resource; }
  public function getLastQuery() { return $this->lastQuery; }

  abstract public function connect();

  abstract public function close(); 

  abstract public function execute($sql);

  abstract protected function getLastErrorMessage();

  abstract public function getTableInfo($name);

}

abstract class SQLResultSet extends Object {

  protected $result, $connection;
  protected $row= array();

  public function SQLResultSet($result, SQLConnection $connection) {
    $this->result= $result;
    $this->connection  = $connection;
  }

  public abstract function next();

  public function getRow() { return $this->row; }

  public function __get($name) {
    if(isset($this->row[$name])) return $this->row[$name];
    throw new SQLException('Cannot get the value of "' . $name . '" no such field!');
  }

}

class SQLiteResultSet extends SQLResultSet {

  public function next() {
    $this->row= sqlite_fetch_array( $this->result );
    return $this->row ? true : false;
  }

}

abstract class SQLTableInfo extends Object {

  protected $fields, $name, $connection;

  public function SQLTableInfo($name, SQLConnection $connection) {
    $this->name= $name;
    $this->connection= $connection;
    $this->fields= array();
  }

  public function getFields() { return $this->fields; }

  abstract public function initFields( );

}

class SQLiteTableInfo extends SQLTableInfo {

  public function initFields() {
    $sql= 'PRAGMA table_info('.$this->name.')';
    $rs= $this->connection->execute( $sql );
    while( $rs->next() ) {
      // xxx: type.
      $f= new Field( $rs->name, $rs->pk, $rs->type );
    }
  }

}

class SQLiteConnection extends SQLConnection {

  public function SQLiteConnection() {
    $this->database= 'db/aymo.sqlite';
  }

  public function connect() {
    try {
      $this->resource= sqlite_open( $this->database );
    } catch (Error $err) {
      throw new SQLException( $err->getMessage() );
    }
  }

  public function execute($sql) {
    $this->lasQuery= $sql;
    try {
      $result= sqlite_query( $this->resource, $sql, SQLITE_ASSOC );
    } catch (Error $err) {
      throw new SQLException( $err->getMessage() );
    }
    return new SQLiteResultSet($result, $this);
  }

  public function close() {
    sqlite_close( $this->resource );
  }

  public function getLastErrorMessage() {
    return sqlite_error_string( sqlite_last_error($this->resource) );
  }
  
  // xxx. cache.
  public function getTableInfo( $name ) {
    $table_info= new SQLiteTableInfo( $name, $this );
    $table_info->initFields();
  }

}



