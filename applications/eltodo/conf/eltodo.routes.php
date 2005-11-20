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
}
?>
