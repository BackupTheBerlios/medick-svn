<?php

interface IPlugin {

  /*
   * Should create a new Instance
   */ 
  public function __construct( ContextManager $context );

  /*
   * Should return the plugin metadata array
   */
  public function metadata();

  /**
   * Should return the plugin name from metadata array
   */ 
  public function name();

}
