<?php

// $Id$

interface ITemplateEngine {

  public function partial($partial);

  public function render($file);

  public function assign($name, $value);

}

