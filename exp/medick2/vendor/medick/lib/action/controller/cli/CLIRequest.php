<?php

// $Id: $

//
// xxx. should be able to handle:
//  
//  php script.php --uri=/foo/bar --method=GET
//
// just to make sure we emulate http requests.
// 
class CLIRequest extends Request {

  public $uri= "";

  public function __construct() {
    $this->uri= '/' . join('/', array_slice($_SERVER['argv'], 1, $_SERVER['argc']));
  }

  public function toString() {
    return sprintf('cli: %s', $this->uri);
  }

}

