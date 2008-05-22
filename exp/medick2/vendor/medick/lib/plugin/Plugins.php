<?php

// $Id$

class Plugins extends Object {

  static private $registry= array();

  public static function loaded($plugin_name) {
    return isset(Plugins::$registry[$plugin_name]);
  }

  public static function add( IPlugin $plugin ) {
    Plugins::$registry[$plugin->name()]= $plugin;
  }

  // should return IPlugin[]
  public static function discover( ContextManager $context ) {
    // XXX: try to load plugins from <plugins> section
    if($context->config()->property( 'plugin.autodiscovery' ) === false) return;

    $context->logger()->debug( strtolower(__METHOD__) . ' [hint: set `plugin.autodiscovery` to false to disable plugins]');

    // XXX: plugins.path then fail to default
    foreach(new DirectoryIterator( MEDICK_PATH . '/../../vendor/plugins' ) as $plugin_path) {
      $plugin_load_file = $plugin_path->getPathname() . DIRECTORY_SEPARATOR . 'init.php';
      if( $plugin_path->isDir() && is_file($plugin_load_file) && require($plugin_load_file)) {
        $class= Plugins::plugin_class_name($plugin_path);
        Plugins::add( new $class($context) );
        $context->logger()->debugf('%s --> %s', str_replace(MEDICK_PATH, '${'.$context->config()->application_name().'}', $plugin_load_file), $class );
      }
    }

  }

  // XXX: foo_bar should be FooBar
  private static function plugin_class_name(DirectoryIterator $plugin_path) {
    $plugin_name= $plugin_path->getFilename();
    return ucfirst($plugin_name) . 'Plugin';
  }

}
