<?php
// $Id$
// This file is part of ActiveRecord5, a Medick (http://medick.locknet.ro) Experiment

set_include_path('libs');

// medick base stuff
include_once('ar5_base.php');
// not yet ported classes
include_once('ar5_sql.php');
// medick trunk ported classes
include_once('context/configurator/XMLConfigurator.php');

// driver
include_once('active/record/drivers/sqlite/sqlite.php');

include_once('active/record/Base.php');

// ----------
// tests :)
// ----------

class User extends ActiveRecord { 

  public static function find() {
    $args= func_get_args();
    return ActiveRecord::build(new SQLBuilder(__CLASS__, $args));
  }

}

$config= new XMLConfigurator('conf/aymo.xml', 'test');
ActiveRecord::setConnectionDsn( $config );

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
