<?php
// eltodo.routes.php
// $Id$

{
    { // solves: /, /project.html, /project/index.html
        $project= new Route('project');
        $project->setAction('all');
        $project->setName('default');
        $map->add($project);
    }
    
    { // solves /project/create.html
        $create_project= new Route('project', 'create');
        $create_project->add(new RouteParam('name'));
        $create_project->setFailure($project);
        $map->add($create_project);
    }
    
    { // solves: /project/delete/{id}.html
        $delete_project= new Route('project', 'delete');
        $delete_project->add(new RouteParam('id'));
        $delete_project->setFailure($project);
        $map->add($delete_project);
    }
}
?>
