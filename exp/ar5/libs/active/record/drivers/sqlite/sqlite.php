<?php
// $Id$

class SQLiteResultSet extends SQLResultSet {

  public function next() {
    $this->row= sqlite_fetch_array( $this->result );
    return (bool)$this->row;
    // return $this->row ? true : false;
  }

}

class SQLiteTableInfo extends SQLTableInfo {

  public function initFields() {
    $sql= 'PRAGMA table_info('.$this->name.')';
    $rs= $this->connection->execute( $sql );
    while( $rs->next() ) {
      // xxx: type.
      $fulltype= $rs->type; // varchar(255);
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
      $this->add( new Field( $rs->name, $rs->pk, $type, $size ) );
    }
  }

}

class SQLitePreparedStatement extends SQLPreparedStatement {

  protected function escape( $value ) {
    return sqlite_escape_string( $value );
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
  
  // xxx. cache.
  public function getTableInfo( $name ) {
    $table_info= new SQLiteTableInfo( $name, $this );
    $table_info->initFields();
    return $table_info;
  }

  public function prepare( $sql ) {
    return new SQLitePreparedStatement($this, $sql);
  }

}

