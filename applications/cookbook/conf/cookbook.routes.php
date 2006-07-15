<?php
// routes.php
// $Id: application.routes.php 316 2006-01-03 21:27:58Z aurelian $
// @TODO: write some documentation about this file

$map= Map::getInstance();
$map->add(new Route(':controller/:action/:id'));
?>
