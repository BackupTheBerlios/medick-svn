<?php
// $Id$

class SQLException extends MedickException {  }

// xxx.
class SQLType extends Object {

  // sql type to php type
  public static function getPhpType( $type ) {
    if( $type == 'integer' || $type == 'int') return 'Integer';
    else return 'String';
    // elseif( $type == 'varchar' || $type == 'string' || $type == 'text') return 'String';
    // elseif( $type == 'timestamp' || $type == 'time' || $type == 'date') return 'Time';
    // else throw new SQLException('Unknow type: "' . $type . '"');
  }

}

abstract class SQLResultSet extends Object implements ArrayAccess {

  protected $result, $connection;
  protected $row= array();

  public function SQLResultSet($result, SQLConnection $connection) {
    $this->result= $result;
    $this->connection  = $connection;
  }

  public function offsetExists($offset) {
    return isset( $this->row[$offset] );
  }

  public function offsetGet($offset) {
    return $this->row[$offset];
  }

  public function offsetSet($offset, $value) {
    throw new MedickError("A ResultSet is read-only!");
  }

  public function offsetUnset($offset) {
    throw new MedickError("A ResultSet is read-only!");
  }

  public function getRow() { 
    return $this->row;
  }

  public function __get($name) {
    if(isset($this->row[$name])) return $this->row[$name];
    throw new SQLException('Cannot get the value of "' . $name . '" no such field!');
  }

  abstract public function next();

}


