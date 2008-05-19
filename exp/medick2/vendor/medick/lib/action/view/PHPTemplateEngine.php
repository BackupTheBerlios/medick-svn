<?php

// $Id$

class PHPTemplateEngine extends AbstractTemplateEngine {

  public function partial($partial) {

  }

  public function render( $file ) {

    // XXX: loop view paths
    if (false === file_exists( $file )) {
      throw new Exception( 'Cannot render template: `' . $file . '`, no such file!' );
    }

    // XXX: load helper['s]

    if (sizeof($this->vars) > 0) {
      extract($this->vars,EXTR_SKIP);
    }

    $this->context->logger()->debug(sprintf('ready to render `%s` (+ %.3f sec.)', $file, $this->context->timer()->tick()));

    ob_start();
    include_once( $file );
    $c = ob_get_contents();
    ob_end_clean();
    $this->context->logger()->debug(sprintf('template parsed (+ %.3f sec.)', $this->context->timer()->tick()));
    return $c;
  }

}

