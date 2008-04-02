<?php

class Map extends Object {

  private $config;

  private $routes;

  public function __construct( IConfigurator $config ) {
    $this->config= $config;
    $this->routes= array();
  }

  public function find_route(Request $request) {
    if(empty($this->routes)) $this->load_routes();
    foreach($this->routes as $route) {
      if($route->match($request)) return $route;
    }
    throw new Exception( "Couldn't find a route to match your request." );
  }

  /*
   * Collects routes from Configurator and then from plugins
   */ 
  private function load_routes() {
    // 1. config.xml routes
    foreach($this->config->routes() as $r) {
      $this->routes[]= new Route( (string)trim($r['value']) );
    }
    // 2. plugins routes

    // XXX:
    //  throw exception if 0 routes? or load the __default always?
  }

}

