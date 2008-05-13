<?php

//
// $Id: $
//

class Dispatcher extends Object {

  private $context;

  private $logger;

  private $plugins;

  public function __construct(ContextManager $context) {
    // context
    $this->context= $context;

    // plugins
    $this->plugins= Plugins::discover( $this->context );

  }

  public function dispatch() {
    $request = (php_sapi_name()=='cli')? new CLIRequest() : new HTTPRequest();
    $response= new HTTPResponse();

    $this->context->logger()->debug( 
      sprintf('medick v.$%s ready to dispatch (took %.3f sec. to boot)', Medick::version(), $this->context->timer()->tick()));

    try {
      Router::recognize( $request, $this->context )->process( $request, $response );
      // ->dump();
    } catch(Exception $ex) {
      echo sprintf('Exception: %s with message: %s', get_class($ex), $ex->getMessage());
    }
  }

}

