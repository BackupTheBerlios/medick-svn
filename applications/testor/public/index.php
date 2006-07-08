<?php
// $Id$

// 
// This file is part of testor project
// auto-generated on 2006 Mar 22 21:16:32 with medick version: 0.2.2-svn
// 

// complete path to medick boot.php file.
include_once('/wwwroot/medick/trunk/boot.php');
// complete path to testor.xml
// and environment to load
$d= new Dispatcher(
          ContextManager::load(
            '/wwwroot/medick/applications/testor/conf/testor.xml',
            'gremlin')
        );
$d->dispatch();
