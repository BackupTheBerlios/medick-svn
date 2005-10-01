<?php
// routes.php
// $Id$
// @TODO: write some documentation about this file :)

// an example of route configuration in medick.

// $map= MedickRegistry::get('__map');

{
    { // /todo/edit/id.html
        $route= new Route('todo', 'edit');
        $route->setAccess(1);
        $route->add(new RouteParam('id'));
        $map->add($route);
    }
    
    { // /todo/create.html
        $route= new Route('todo', 'create');
        $route->setAccess(1);
        $param= new RouteParam('description');
        $route->add($param);
        $param= new RouteParam('done');
        $route->add($param);
        $map->add($route);
    }

    { // /todo/update.html
        $route= new Route('todo', 'update');
        $route->setAccess(1);
        $param= new RouteParam('description');
        $route->add($param);
        $param= new RouteParam('done');
        $route->add($param);
        $map->add($route);
    }
    
    { // /todo/delete.html
        $route= new Route('todo', 'delete');
        $route->setAccess(1);
        $route->add(new RouteParam('id'));
        $map->add($route);
    }
}
?>
