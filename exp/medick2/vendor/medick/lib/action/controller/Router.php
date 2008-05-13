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

    // xxx: should call plugins special __before_controller methods?

    // XXX: should load additional paths from plugins?

    // 1. class name -> request->parameter('controller') . 'Controller'
    // 2. file name  -> request->parameter('controller') . '_controller.php'

    $path= APP_PATH; // -> this is going to be a list of paths

    $controller_file = $path . '/app/controllers/' . $request->parameter('controller') . '_controller.php';
    $controller_class= ucfirst($request->parameter('controller')) . 'Controller';

    if( false === file_exists($controller_file) ) {
      throw new Exception('Cannot load `'.$controller_file.'`, no such file or directory!'); // -> this is going to be replaced with continue in loop
    }

    require( $controller_file );

    if( false === class_exists($controller_class) ) {
      throw new Exception('Cannot use `'.$controller_class.'`, no such class!'); // -> this is going to be replaced with continue in loop
    }

    $rclass= new ReflectionClass($controller_class);

    if( false === ($rclass->getParentClass() || $rclass->getParentClass() == 'ApplicationController' || $rclass->getParentClass() == 'ActionController')) {
      throw new Exception('Wrong defintion of class: ' . $controller_class);
    }

    $this->context->logger()->debug(str_replace(APP_PATH, '${'.$this->context->config()->application_name().'}', $controller_file) . ' --> ' . $controller_class);

    return $rclass->newInstance( $this->context );

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

