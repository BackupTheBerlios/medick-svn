<?php

// Foo Plugin loadder
function __foo_plugin_autoload($class) {
  $file= 'lib'.DIRECTORY_SEPARATOR.$class.'.php';
  if(is_file( dirname(__FILE__) . DIRECTORY_SEPARATOR . $file )) {
    return require $file;
  }
}

spl_autoload_register('__foo_plugin_autoload');

