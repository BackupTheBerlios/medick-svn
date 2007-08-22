<?php
// $Id$

set_include_path('libs');

include_once('ar5_base.php');

// include_once('context/configurator/XMLConfigurator.php');

include_once('active/record/SQLField.php');
include_once('active/record/SQLBuilder.php');
include_once('active/record/SQLCommand.php');

include_once('active/record/drivers/abstract/SQLConnection.php');
include_once('active/record/drivers/abstract/SQLPreparedStatement.php');
include_once('active/record/drivers/abstract/SQLTableInfo.php');

include_once('ar5_sql.php');

include_once('active/record/drivers/sqlite/sqlite.php');

include_once('active/support/Inflector.php');
// include_once('active/support/Reflector.php');

class ActiveRecordException extends MedickException { }

class ActiveRecord extends Object {

  protected $__class_name;
  protected $__table_name;
  protected $__fields;
  protected $__primary_key;

  public function ActiveRecord( $params=array() ) {
    $this->__class_name  = $this->getClassName();
    $this->__table_name  = Inflector::tabelize( $this->__class_name );
    $this->__fields      = ActiveRecord::connection()->getTableInfo( $this->__table_name )->getFields();
    $this->__primary_key = current( array_filter( $this->__fields, array($this,'__pk') ));
    foreach($params as $key=>$value) {
      $this->$key= $value;
    }
  }

  public function __set($name, $value) {
    if( isset($this->__fields[$name]) ) return $this->__fields[$name]->alter( $value );
    throw new ActiveRecordException('No such field "' . $name . '"');
  }

  public function __get($name) {
    if( isset($this->__fields[$name]) ) return $this->__fields[$name]->getValue();
    throw new ActiveRecordException('No such field "' . $name . '"');
  }

  public function getPrimaryKey() {
    return $this->__primary_key;
  }

  public function save() {
    return $this->__primary_key->isAffected() ? $this->update() : $this->insert();
  }

  public function insert() {
    $fields= $this->getAffectedFields();
    $sql= 'insert into ' . $this->__table_name
          . ' (' . implode(',', array_keys($fields)) . ')'
          . ' values (' . substr(str_repeat('?,', count($fields)), 0, -1) . ')';
    $this->performQuery($sql, $fields);
    $this->__primary_key->alter( self::connection()->nextId() );
  }

  public function update() {
    $fields= $this->getAffectedFields();
    if( sizeof($fields) < 1 ) return 0; // no harm, primary_key will be always affected, otherwise it will be an insert.
    $sql= 'update ' . $this->__table_name . ' set ';
    $sql .= implode('=?, ', array_keys($fields)) . '=? ';
    $sql .= 'where ' . $this->__primary_key->getName() . '=' . $this->__primary_key->getValue();
    $this->performQuery($sql, $fields);
  }

  private function performQuery( $sql, $fields ) {
    $stmt= self::connection()->prepare( $sql );
    $stmt->populateValues( $fields );
    $r= $stmt->executeUpdate();
    $stmt->close();
    $this->reset();
    return $r;
  }

  private function getAffectedFields() {
    return array_filter( $this->__fields, array($this, '__affectedField') );
  }

  private function reset() {
    return array_walk( $this->__fields, array($this,'__notAffected'));
  }

  // callback for array_filter
  private function __affectedField( SQLField $field ) {
    return $field->isAffected();
  }
  // callback for array_filter
  private function __pk( SQLField $field ) {
    return $field->isPk();
  }

  // callback for array_walk
  private function __notAffected( SQLField $field ) {
    if( $field->isAffected() && !$field->isPk() ) $field->setAffected(false);
  }

  // -----------
  // static
  // -----------
  protected static $__table_info = null;
  protected static $__connection = null;

  public static function connection() {
    if(self::$__connection===null) {
      self::$__connection= new SQLiteConnection();
      self::$__connection->connect();
    }
    return self::$__connection;
  }

  public static function find() {
    throw new MedickException('ActiveRecord::find() must be implemented in child class.');
  }

  public static function build( SQLBuilder $builder ) {
    return $builder->execute();
  }

}

class User extends ActiveRecord { 

  public static function find() {
    $args= func_get_args();
    return ActiveRecord::build(new SQLBuilder(__CLASS__, $args));
  }

}


$u = new User();
$u->firstname= 'aurelian2';
$u->lastname = 'oancea';
$u->email= 'oancea@gmail.com';
$u->password=md5('foo');
$u->save();

$u->password=md5('bar');
$u->save();
$u->save();

$user = new User(array('firstname'=>'oancea2', 'lastname'=>'mandinga'));
$user->email= 'oancea@yahoo.com';
$user->password= md5('bau-bau');
$user->save();


$users= User::find();

foreach($users as $user) {
  // var_dump($user);
  echo '[ ' . $user->id . ' ]> ' . $user->firstname . "\n";
}

?>
