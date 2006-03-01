<?php
// eltodo.routes.php
// $Id$

$map= Map::getInstance();
$map->add(new Route('/project/all', Route::WELCOME, array('controller'=>'project', 'action'=>'all')));
$map->add(new Route(':controller/:action/:id'));

?>
