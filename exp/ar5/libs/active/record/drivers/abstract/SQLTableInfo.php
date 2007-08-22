<?php
// $Id$

abstract class SQLTableInfo extends Object {

  protected $name, $connection;
  private $fields;

  public function SQLTableInfo($name, SQLConnection $connection) {
    $this->name= $name;
    $this->connection= $connection;
    $this->fields= array();
  }

  public function add(SQLField $field) {
    $this->fields[$field->getName()]= $field;
  }

  public function getFields() { 
    return $this->fields;
  }

  abstract public function initFields( );

}

