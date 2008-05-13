<?php

class Timer extends Object {
  
  private $start;
  private $end;

  public function __construct($start) {
    $this->start= $start;
    $this->end= null;
  }

  public function tick() {
    $this->end= microtime(true);
    $r= (float)$this->end - (float)$this->start;
    $this->start= $this->end;
    return $r;
  }

}

// $Id: $

class ContextManager extends Object {

  // log everyware!
  private $logger;

  // the config parser/loaded, to have access to configuration options
  private $config;

  // a Map, you get access to Routes using this
  private $map;

  private $timer;

  private function __construct( Iconfigurator $config ) {
    $this->config= $config;
    // configure the logger
    $this->logger= new Logger();
    $this->logger->setFormatter( Logger::formatter($this->config) );
    $this->logger->attachOutputters( Logger::outputters($this->config) );
    // ready?
    $this->logger->debug("\t[".time() . "] `" . $this->config->environment() . "` env. from " . 
      str_replace(APP_PATH, '${'.$this->config->application_name().'}/', $this->config->file()) . " loaded for \${ip}");
    // create a Map for routes.
    $this->map= new Map( $this );
  }

  public function logger() {
    return $this->logger;
  }

  public function config() {
    return $this->config;
  }

  public function map() {
    return $this->map;
  }

  public function timer($start= null) {
    if($this->timer===null) {
      $this->timer= new Timer($start===null? microtime(true) : $start);
    }
    return $this->timer;
  }

  public static function load( $file, $environment ) {
    $start= microtime(true);
    // XXX: factory based on the file type for configurator
    $context= new ContextManager(new XMLConfigurator( $file, $environment ));
    $context->timer($start)->tick();
    return $context;
  }

}
