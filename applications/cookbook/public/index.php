<?php
// $Id: index.php 379 2006-03-18 17:36:03Z aurelian $

// 
// This file is part of cookbook project
// auto-generated on 2006 Jul 15 11:49:36 with medick version: 0.3.0pre1
// 

// complete path to medick boot.php file.
include_once('/wwwroot/medick.release/0.3.0pre1/medick-0.3.0pre1/framework/boot.php');
// complete path to cookbook.xml
// and environment to load
$d= new Dispatcher(
          ContextManager::load(
            '/wwwroot/medick/applications/cookbook/conf/cookbook.xml',
            'localhost')
        );
$d->dispatch();

