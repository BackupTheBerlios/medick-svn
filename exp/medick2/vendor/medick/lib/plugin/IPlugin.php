<?php

interface IPlugin {

  /*
   * Should create a new Instance
   */ 
  public function __construct(IConfigurator $config, ILogger $logger);

  /*
   * Should return the plugin name
   */
  public function name();

}
