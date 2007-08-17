<?php
// $Id$

include_once('ar5_base.php');
include_once('ar5_sql.php');
include_once('libs/active/support/Inflector.php');

class ActiveRecord extends Object {

  protected $__class_name;

  protected $__table_name;

  protected $__fields;

  public function ActiveRecord(  ) {
    $this->__class_name= $this->getClassName();
    $this->__table_name= Inflector::tabelize( $this->__class_name );
    $this->__fields    = ActiveRecord::connection()->getTableInfo( $this->__table_name )->getFields();
  }


  protected static $__table_info = null;

  protected static $__connection = null;

  public static function connection() {
    if(self::$__connection===null) {
      self::$__connection= new SQLiteConnection();
      self::$__connection->connect();
    }
    return self::$__connection;
  }

}

class User extends ActiveRecord { 

}

$u = new User();
$u->firstname= 'aurelian';
$u->insert();

// $ar= ActiveRecord::connection()->execute('select * from users');

?>
