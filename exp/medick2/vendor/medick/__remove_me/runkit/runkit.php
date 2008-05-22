<?php

function my_func() {
    return __FUNCTION__;
}

include_once('foo.php');

$php1= new Runkit_Sandbox();
$php1->eval( "include_once('foo.php');Foo::bar();" );

echo "Global Scope: " . Foo::$baz . "--->0\n";

$php2= new Runkit_Sandbox();
$php2['parent_call'] = true;
$php2->eval('include_once("foo.php");');
$php2->eval('Foo::bar();');
$php2->eval('echo "PHP2 Scope: " . Foo::$baz . " --->1\n";');

$php2->eval('echo my_func();');


