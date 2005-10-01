<?php
// routes.php
// $Id$
// @TODO: write some documentation about this file :)

// an example of route configuration in medick.
$map= new Map(); {

    { // /size/all.html
        $route= new Route('size', 'all');
        $route->setName('default');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /size/anew.html
        $route= new Route('size', 'anew');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /style/all.html
        $route= new Route('style', 'all');
        $route->setName('style/all');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /style/anew.html
        $route= new Route('style', 'anew');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /author/all.html
        $route= new Route('author', 'all');
        $route->setName('author/all');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /author/anew.html
        $route= new Route('author', 'anew');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /subject/all.html
        $route= new Route('subject', 'all');
        $route->setName('subject/all');
        $route->setAccess(1);
        $map->add($route);
    }
    
    { // /subject/anew.html
        $route= new Route('subject', 'anew');
        $route->setAccess(1);
        $map->add($route);
    }    
    
    // {{{ tbr.
    
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
    
    // }}}
}
?>
