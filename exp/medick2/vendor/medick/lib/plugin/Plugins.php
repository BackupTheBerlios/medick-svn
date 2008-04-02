<?php

class Plugins extends Object {

  static private $registry= array();

  public static function loaded($plugin_name) {
    return isset(Plugins::$registry[$plugin_name]);
  }

  public static function add( IPlugin $plugin ) {
    Plugins::$registry[$plugin->name()]= $plugin;
  }

  //
  // XXX: 
  // -> foo_bar should be FooBar
  //
  private static function plugin_class_name($plugin_path) {
    $plugin_name= $plugin_path->getFilename();
    return  ucfirst($plugin_name) . 'Plugin';
  }

  // should return IPlugin[]
  public static function discover( IConfigurator $config, ILogger $logger ) {

    if($config->property( 'plugin.autodiscovery') === false) return;
    
    //
    // XXX.
    // -> plugin.path
    // -> multiple paths?
    // -> <plugin> section
    //

    foreach(new DirectoryIterator( MEDICK_PATH . '/../../vendor/plugins' ) as $plugin_path) {
      $plugin_load_file = $plugin_path->getPathname() . DIRECTORY_SEPARATOR . 'init.php';
      if( $plugin_path->isDir() && is_file($plugin_load_file) && require($plugin_load_file)) {
        $class= Plugins::plugin_class_name($plugin_path);
        Plugins::add( new $class($config, $logger) );
        $logger->debugf( "[frw] %s --> %s", str_replace(MEDICK_PATH, '${'.$config->application_name().'}', $plugin_load_file), $class );
      }
    }

  }

}

