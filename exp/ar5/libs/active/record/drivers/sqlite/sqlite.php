<?php
// $Id$

class SQLiteRecordsIterator extends Object implements Iterator {

  private $result;

  private $class;

  public function SQLiteRecordsIterator( $result, ReflectionClass $class ) {
    $this->class= $class;
    $this->result= $result;
  }

  // Rewind the Iterator to the first element.
  public function rewind() {
    return sqlite_rewind( $this->result );
  }

  // Returns the current element
  public function current() {
    return $this->class->newInstance( sqlite_current( $this->result ) );
  }

  // Return the key of the current element.
  public function key() {
    return sqlite_key( $this->result );
  }

  // Moves the cursor to the next element.
  public function next() {
    return sqlite_next( $this->result );
  }

  // Check if there is a current element after calls to rewind() or next().
  public function valid() {
    return sqlite_valid( $this->result );
  }

}

class SQLiteResultSet extends SQLResultSet {

  public function next() {
    $this->row= sqlite_fetch_array( $this->result );
    return (bool)$this->row;
  }

}

class SQLiteTableInfo extends SQLTableInfo {

  public function initFields() {
    echo ".";
    $sql= 'PRAGMA table_info('.$this->name.')';
    $rs= $this->connection->execute( $sql );
    while( $rs->next() ) {
      // xxx: type.
      $fulltype= $rs['type']; // varchar(255);
      $size=0;
      if (preg_match('/^([^\(]+)\(\s*(\d+)\s*,\s*(\d+)\s*\)$/', $fulltype, $matches)) {
        $type = $matches[1];
        $size = $matches[2];
        // $scale = $matches[3]; // aka precision    
      } elseif (preg_match('/^([^\(]+)\(\s*(\d+)\s*\)$/', $fulltype, $matches)) {
        $type = $matches[1];
        $size = $matches[2];
      } else {
        $type = $fulltype;
      }
      // add field
      $this->add( new SQLField( $rs['name'], $rs['pk'], $type, $size ) );
    }
  }

}

class SQLitePreparedStatement extends SQLPreparedStatement {

  protected function escape( $value ) {
    return sqlite_escape_string( $value );
  }

  public function getRecordsIterator( $result, ReflectionClass $class ) {
    return new SQLiteRecordsIterator( $result, $class );
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

  public function exec( $sql ) {
   $this->lastQuery= $sql;
    try {
      return sqlite_query( $this->resource, $sql, SQLITE_ASSOC );
    } catch (Error $err) {
      throw new SQLException( $err->getMessage() );
    }
  }

  public function execute( $sql ) {
    return new SQLiteResultSet( $this->exec( $sql ), $this );
  }

  public function getUpdateCount( $rs=null ) {
    return sqlite_changes( $this->resource );
  }

  public function nextId() {
    return sqlite_last_insert_rowid( $this->resource );
  }

  public function close() {
    sqlite_close( $this->resource );
  }

  public function getLastErrorMessage() {
    return sqlite_error_string( sqlite_last_error($this->resource) );
  }

  private static $__table_info_storage;

  public function getTableInfo( $name, $force= false ) {
    if( $force || !isset(self::$__table_info_storage[$name]) ) {
      self::$__table_info_storage[$name]= new SQLiteTableInfo( $name, $this );
      self::$__table_info_storage[$name]->initFields();
    }
    return self::$__table_info_storage[$name];
  }

  public function prepare( $sql ) {
    return new SQLitePreparedStatement($this, $sql);
  }

}

