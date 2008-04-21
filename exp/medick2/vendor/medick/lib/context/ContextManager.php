<?php

class ContextManager extends Object {

  private $logger;

  private $config;

  public function __construct( Iconfigurator $config ) {
    $this->config= $config;
    // configure the logger
    $this->logger= new Logger();
    $this->logger->setFormatter( Logger::formatter($this->config) );
    $this->logger->attachOutputters( Logger::outputters($this->config) );

  }

  public function logger() {
    return $this->logger;
  }

  public function config() {
    return $this->config;
  }

  public static function load( $xml_file, $environment ) {
    // XXX: factory based on the file type for configurator
    return new ContextManager(new XMLConfigurator( $xml_file, $environment ));
  }

}
