<?php

class Object {

  public function getClassName() {
    return get_class($this);
  }

  public function toString() {
    
  }

  public function __toString() {
    return $this->toString();
  }

}

