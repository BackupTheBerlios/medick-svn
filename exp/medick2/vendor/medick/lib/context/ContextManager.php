<?php

class ContextManager extends Object {

  public static function load($xml_file, $environment) {
    // XXX: factory based on the file type
    return new XMLConfigurator( $xml_file, $environment );
  }

}
