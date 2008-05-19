<?php

// $Id$

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

    // 1. class name -> request->parameter('controller') . 'Controller'
    // 2. file name  -> request->parameter('controller') . '_controller.php'

    $path= APP_PATH; // -> this is going to be a list of paths

    $controller_file = $path . 'app/controllers/' . $request->parameter('controller') . '_controller.php';
    $controller_class= ucfirst($request->parameter('controller')) . 'Controller';

    if( false === file_exists($controller_file) ) {
      throw new Exception('Cannot load `' . $controller_file . '`, no such file or directory!'); // -> this is going to be replaced with continue in loop
    }

    require( $controller_file );

    if( false === class_exists($controller_class) ) {
      throw new Exception('Expected `' . $controller_file . '` to define `'.$controller_class.'`'); // -> this is going to be replaced with continue in loop
    }

    $rclass= new ReflectionClass($controller_class);

    if( false === ($rclass->getParentClass() || $rclass->getParentClass() == 'ApplicationController' || $rclass->getParentClass() == 'ActionController')) {
      throw new Exception('Expected `' . $controller_class . '` to extend ApplicationController(recommended) or ActionControler');
    }

    $this->context->logger()->debug(str_replace(APP_PATH, '${'.$this->context->config()->application_name().'}', $controller_file) . ' --> ' . $controller_class);

    return $rclass->newInstance( $this->context );

  }

  /*
   * Should return a controller instance
   */ 
  public static function recognize(Request $request, ContextManager $context ) {
    // XXX: request URI hack, for medick installation in subfolders (e.g. `medick2` as base)
    // XXX: test with other servers and other types of PHP installations
    // $request->uri= substr($request->uri, strlen($context->config()->property('base', true)));

    $router= new Router( $context->map()->find_route( $request ), $context);
    return $router->create_controller( $request );
  }

}

