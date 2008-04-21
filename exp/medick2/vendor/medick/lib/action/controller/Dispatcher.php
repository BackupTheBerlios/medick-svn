<?php

//
// $Id: $
//

/*
 * 
 */ 
class Dispatcher extends Object {

  private $context;

  private $logger;

  private $plugins;

  public function __construct(ContextManager $context) {
    // context
    $this->context= $context;

    // ready to log stuff
    $this->context->logger()->debugf( '[frw.action_controller] Medick v.$%s ready to dispatch!', Medick::version() );

    // plugins
    $this->plugins= Plugins::discover( $this->context );

  }

  public function dispatch() {
    $request = new HTTPRequest();
    $response= new HTTPResponse();
    try {
      Router::recognize( $request, $this->context );
      // ->process( $request, $response )->dump();
    } catch(Exception $ex) {
      echo sprintf('Exception: %s with message: %s', get_class($ex), $ex->getMessage());
    }
  }

}

