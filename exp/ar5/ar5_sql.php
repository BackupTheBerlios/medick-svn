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

class Field extends Object {

  private $name, $value, $pk, $type, $size, $affected;

  public function Field($name, $pk=false, $type='int', $size=0, $value= null, $affected=false) {
    $this->name=  $name;
    $this->value= $value;
    $this->pk= (bool)$pk;
    $this->size= (int)$size;
    $this->type= SQLType::getPhpType( strtolower($type) );
    $this->affected= (bool)$affected;
  }

  public function getName() { return $this->name; }

  public function getValue() { return $this->value; }
  public function setValue($value) { $this->value= $value;}

  public function setAffected($val) { $this->affected= (bool)$val; }
  public function isAffected() { return (bool)$this->affected; }

  public function isPk() { return (bool)$this->pk; }

  public function getType() { return $this->type; }

  public function alter( $value ) {
    $this->value    = $value;
    $this->affected = true;
  }

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

abstract class SQLTableInfo extends Object {

  protected $name, $connection;
  private $fields;

  public function SQLTableInfo($name, SQLConnection $connection) {
    $this->name= $name;
    $this->connection= $connection;
    $this->fields= array();
  }

  public function add(Field $field) {
    $this->fields[$field->getName()]= $field;
  }

  public function getFields() { return $this->fields; }

  abstract public function initFields( );

}

