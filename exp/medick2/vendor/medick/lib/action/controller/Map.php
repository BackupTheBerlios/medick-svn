<?php

/*
 * A Map holds Route[]
 */ 
class Map extends Object {

  // current context
  private $context;

  // routes collection
  private $routes;

  /*
   * Context is needed since routes are defined on it
   */ 
  public function __construct( ContextManager $context ) {
    $this->context= $context;
    $this->routes= array();
  }

  /*
   * Finds a Route
   */
  public function find_route(Request $request) {
    if(empty($this->routes)) $this->load_routes();
    foreach($this->routes as $route) {
      if($route->match($request)) return $route;
    }
    throw new Exception( "Couldn't find a route to match your request." );
  }

  /*
   * Collects routes from Context->Configurator and then from plugins
   */ 
  private function load_routes() {
    // 1. config.xml routes
    foreach($this->context->config()->routes() as $r) {
      $this->routes[]= new Route( (string)trim($r['value']) );
    }
    // 2. plugins routes

    // XXX:
    //  throw exception if 0 routes? or load the __default always?
  }

}

