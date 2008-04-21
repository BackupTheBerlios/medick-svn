<?php

class Object {

  public function class_name() {
    return get_class($this);
  }

  public function toString() {
    
  }

  public function __toString() {
    return $this->toString();
  }

}

