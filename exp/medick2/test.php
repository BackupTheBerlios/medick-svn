<?php

include_once('boot.php');

// complete path to cFields.xml
// and environment to load
$d= new Dispatcher(
          ContextManager::load(
            '/home/aurelian/Code/medick/exp/medick2/config/cfields.xml',
            'test')
        );
$d->dispatch();

