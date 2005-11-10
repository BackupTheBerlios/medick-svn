<?php
// routes.php
// $Id: application.routes.php 148 2005-10-01 15:16:47Z aurelian $
// @TODO: write some documentation about this file :)

// an example of route configuration in medick.

{

    { // /todo.html
        $todo_route= new Route('todo');
        $todo_route->setAction('all');
        $todo_route->setAccess(0);
        $todo_route->setName('default');
        $map->add($todo_route);
    }

    { // /todo/all.html
        $all= new Route('todo', 'all');
        $all->setAccess(0);
        $map->add($all);
    }
    

    { // toogle_checkbox
        $toggle_checkbox_route= new Route('todo', 'checkbox');
        $toggle_checkbox_route->setAccess(1);
        $toggle_checkbox_route->add(new RouteParam('id'));
        $toggle_checkbox_route->setFailure($todo_route);
        $map->add($toggle_checkbox_route);


    }
    
    { // /todo/edit/id.html
        $edit_route= new Route('todo', 'edit');
        $edit_route->setAccess(1);
        $edit_route->add(new RouteParam('id'));
        $edit_route->setFailure($todo_route);
        $map->add($edit_route);
    }
    
    { // /todo/create.html
        $create_route= new Route('todo', 'create');
        $create_route->setAccess(1);
        $create_route->add(new RouteParam('description'));
        $create_route->setFailure($todo_route);
        $map->add($create_route);
    }

    { // /todo/update.html
        $route= new Route('todo', 'update');
        $route->setAccess(1);
        $param= new RouteParam('id');
        $route->add($param);
        $param= new RouteParam('description');
        $route->add($param);
        $param= new RouteParam('done');
        $route->add($param);
        $route->setFailure($edit_route);
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
