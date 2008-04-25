<?php

// $Id: $

class Router extends Object {

  // current Route
  private $route;

  // current context
  private $context;

  private function __construct( Route $route, ContextManager $context ) {
    $this->route= $route;
    $this->context= $context;
  }

  // should return a Controller Instance
  public function create_controller( $request ) {

    Medick::dump( $request );

  }

  /*
   * Should return a controller instance
   */ 
  public static function recognize(Request $request, ContextManager $context ) {
    // create a Map of loaded Routes
    // $map= new Map( $context );
    // save it to the current context
    // $context->map( $map );

    $router= new Router( $context->map()->find_route( $request ), $context);
    return $router->create_controller( $request );
    
    // $route= $map->find_route( $request );
    // return $route->create_controller();
  }

}

