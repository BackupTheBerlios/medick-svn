<?php

class Foo {
  public static $baz = 0;
  public static function bar(){
    self::$baz= 1;
  }
}

