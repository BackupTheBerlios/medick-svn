<?php

class FooPlugin extends Object implements IPlugin {

  //
  // array(
  //    'name'   => $name,
  //    'author' => $author,
  //    'version'=> $version,
  //    'url'    => $url
  //  )

  public function __construct( IConfigurator $config, ILogger $logger ) {
    // $logger->debugf( "%s loaded", $this->name() );
  }

  public function name() {
    return "FooPlugin";
  }

}

