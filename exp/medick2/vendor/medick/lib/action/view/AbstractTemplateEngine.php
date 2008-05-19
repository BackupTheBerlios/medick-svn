<?php

// $Id$

abstract class AbstractTemplateEngine extends Object implements ITemplateEngine {

  protected $vars;

  protected $context;

  protected $controller;

  public function __construct(ContextManager $context, ActionController $controller) {
    $this->context= $context;
    $this->controller= $controller;
    $this->vars= array();
  }

  public function assign($name, $value) {
    $this->vars[$name]= $value;
  }

}

