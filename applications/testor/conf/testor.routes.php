<?php
// routes.php
// $Id$
// @TODO: write some documentation about this file

$map= Map::getInstance();
$map->add(new Route('/main/index', Route::WELCOME, array('controller'=>'main', 'action'=>'index')));

$route= new Route(':controller/:number');
$route->setDefault('action','index');
$route->setRequirement('number', '/([0-9])/');
$map->add($route);

$map->add(new Route(':controller/:action/:id'));
?>
