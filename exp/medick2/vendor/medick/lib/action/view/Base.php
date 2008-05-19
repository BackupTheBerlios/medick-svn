<?php

// $Id$

class ActionView extends Object {

  public static function load( ContextManager $context, ActionController $controller ) {
    // XXX: context will know view paths + the template engine to use
    return new PHPTemplateEngine( $context, $controller );
  }

}

