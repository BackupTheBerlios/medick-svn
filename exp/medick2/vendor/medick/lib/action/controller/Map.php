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
    $this->context->logger()->debug( 'processing Request ' . $request );
    if(empty($this->routes)) $this->load_routes();

    foreach($this->routes as $route) {
      if($route->match($request)) {
        $this->context->logger()->debug( 'matched to Route ' . $route );
        return $route;
      }
    }
    throw new Exception(sprintf('Couldn\'t find a route to match your request: %s', $request));
  }

  /*
   * Collects routes from Context->Configurator and then from plugins
   */ 
  private function load_routes() {
    // 1. config.xml routes
    $config_routes= $this->context->config()->routes();
    foreach( $config_routes as $r ) {
      // xxx. requirements
      $this->routes[]= new Route( (string)trim($r['name']), (string)trim($r['value']), $this->context->config()->route_defaults($r) );
    }
    // 2. plugins routes

    // xxx: throw exception if 0 routes
  }

}

