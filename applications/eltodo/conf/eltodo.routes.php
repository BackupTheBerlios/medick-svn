<?php
// eltodo.routes.php
// $Id$

$map= Map::getInstance();
$map->add(new Route(':controller/:action/:id'));

?>
