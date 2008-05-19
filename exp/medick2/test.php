<?php

// $Id: $

// load the framework
include_once('boot.php');
// dispatch with complete path to cFields.xml and the environment to load
$d= new Dispatcher( ContextManager::load('config/cfields.xml','test') );
$d->dispatch();
