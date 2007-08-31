<?php
// $Id$
// This file is part of ActiveRecord5, a Medick (http://medick.locknet.ro) Experiment

set_include_path('libs');

// Medick base stuff
include_once('ar5_base.php');
// Medick trunk ported classes
include_once('context/configurator/XMLConfigurator.php');
// Driver
include_once('active/record/drivers/sqlite/sqlite.php');
// ActiveRecord
include_once('active/record/Base.php');

// ----------
// tests :)
// ----------

class User extends ActiveRecord { 

  public static function find() {
    $args= func_get_args();
    return ActiveRecord::build( new SQLBuilder( __CLASS__, $args ) );
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

$user= User::find(155);
echo 'User: ' . $user->id . ' ' . $user->firstname . " " . $user->created_at . "\n";

?>
