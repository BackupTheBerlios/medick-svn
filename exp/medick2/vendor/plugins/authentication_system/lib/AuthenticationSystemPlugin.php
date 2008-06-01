<?php

// $Id: $

class AuthenticationSystemPlugin extends AbstractPlugin 
  implements IRoutesPlugin, ILoadPathPlugin {

  private $routes;

  private $load_path;

  public function __construct(ContextManager $context) {

    $this->metadata= array(
      'name'   => $this->class_name(),
      'author' => 'Aurelian Oancea',
      'version'=> 0.1,
      'url'    => 'http://example.com/foo_plugin'
    );
   
    $this->routes= array(
      new Route('__login', '/login', array('controller'=>'account', 'action'=>'login')),
      new Route('__logout', '/logout', array('controller'=>'account', 'action'=>'logout'))
    );

    $this->load_path= dirname(__FILE__) . '/../';

    parent::__construct( $context );

  }

  /* ILoadPathPlugin */
  public function load_path() {
    return $this->load_path;
  }

  /* IRoutesPlugin */
  public function routes() {
    return $this->routes;
  }

  /* IPlugin */
  public function metadata() {
    return $this->metadata;
  }
  
  /* IPlugin */
  public function name() {
    return $this->metadata['name'];
  }

}

