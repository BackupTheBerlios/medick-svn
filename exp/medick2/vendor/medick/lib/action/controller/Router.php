<?php

class Router extends Object {

  private function __construct() {

  }

  public static function recognize(Request $request, IConfigurator $config, Logger $logger) {

    $map= new Map( $config );
    $route= $map->find_route( $request );

    return $route->create_controller();

  }

}

