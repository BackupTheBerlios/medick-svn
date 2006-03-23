<?php
// routes.php
// $Id$
// @TODO: write some documentation about this file

$map= Map::getInstance();
$map->add(new Route(':controller/:action/:id'));
?>
