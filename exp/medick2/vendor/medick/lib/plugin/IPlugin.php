<?php

// $Id$

interface IPlugin {

  public function __construct( ContextManager $context );

  /*
   * Should return the plugin metadata array
   */
  public function metadata();

  public function name();

  public function is_type($name);

}

