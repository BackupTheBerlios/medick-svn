<?php
// $Id: index.php 376 2006-03-12 08:04:30Z aurelian $

// 
// This file is part of eltodo project
// auto-generated on  with medick version: 0.2.1
// 

// complete path to medick boot.php file.
include_once('/wwwroot/medick/trunk/boot.php');
// complete path to eltodo.xml
// and environment to load
$d= new Dispatcher(
          ContextManager::load(
            '/wwwroot/medick/applications/eltodo/conf/eltodo.xml',
            'localhost')
        );
$d->dispatch();

