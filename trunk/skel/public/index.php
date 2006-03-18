<?php
// $Id$

// 
// This file is part of ${app.name} project
// auto-generated on ${date} with medick version: ${medick.version}
// 

// complete path to medick boot.php file.
include_once('${medick.core}${ds}boot.php');
// complete path to ${app.name}.xml
// and environment to load
$d= new Dispatcher(
          ContextManager::load(
            '${app.path}${ds}conf${ds}${app.name}.xml',
            'localhost')
        );
$d->dispatch();

