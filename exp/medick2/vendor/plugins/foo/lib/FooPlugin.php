<?php

// $Id: $

class FooPlugin extends Object implements IPlugin {

  public $metadata;

  public function __construct( ContextManager $context ) {
    $this->metadata= array(
      'name'   => $this->class_name(),
      'author' => 'Joe Doe',
      'version'=> 0.1,
      'url'    => 'http://example.com/foo_plugin'
    );
    $context->logger()->debugf( "Plugin %s loaded", $this->name() );
  }
 
  public function metadata() {
    return $this->metadata;
  }

  public function name() {
    return $this->metadata['name'];
  }

}

