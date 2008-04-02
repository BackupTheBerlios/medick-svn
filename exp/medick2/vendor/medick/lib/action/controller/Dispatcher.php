<?php

//
// $Id: $
//

/*
 * 
 */ 
class Dispatcher extends Object {

  private $configurator;

  private $logger;

  private $plugins;

  public function __construct(IConfigurator $configurator) {
    // configurator
    $this->configurator= $configurator;
    // logger
    $this->logger= new Logger();
    $this->logger->setFormatter( Logger::formatter($this->configurator) );
    $this->logger->attachOutputters( Logger::outputters($this->configurator) );

    $this->logger->debugf( '[frw] Medick v.$%s ready to dispatch!', Medick::version() );

    // plugins
    $this->plugins= Plugins::discover( $this->configurator, $this->logger );
    // collect routes?

  }

  public function dispatch() {
    $request = new HTTPRequest();
    $response= new HTTPResponse();
    try {
      Router::recognize( $request, $this->configurator, $this->logger );
      // ->process( $request, $response )->dump();
    } catch(Exception $ex) {
      echo sprintf('Exception: %s with message: %s', get_class($ex), $ex->getMessage());
    }
  }

}

