<?php

// XXX: Route Segment
class __Segment extends Object {

  private $name;

  private $is_dynamic;

  public function __construct($name, $is_dynamic) {
    $this->name= $name;
    $this->is_dynamic= (bool)$is_dynamic;
  }

  public function name() {
    return $this->name;
  }

  public function is_dynamic() {
    return $this->is_dynamic;
  }

}

class Route extends Object {

  private $definition;

  private $segments;

  private $requirements;

  private $merges;

  private static $old_merges   = array();
  private static $old_defaults = array();

  public function __construct( $definition, Array $requirements= array(), Array $defaults= array() ) {
    $this->definition   = $definition;
    $this->requirements = $requirements;
    $this->defaults     = $defaults;

    // internal structures
    $this->segments     = array();
    $this->merges       = array();

    $this->load_segments();
  }

  private function load_segments() {
    $parts= explode('/', trim($this->definition, '/'));
    foreach ($parts as $key=>$element) {
      if (preg_match('/:[a-z0-9_\-]+/',$element, $match)) {
        $segment= new __Segment(substr(trim($match[0]), 1), true);
      } else {
        $segment= new __Segment($element, false);
      }
      $this->segments[]= $segment;
    }
  }

  private function merge(Request $request) {
    $request->parameter('foo');
    foreach($this->merges as $name => $value) {
      $request->parameter($name, $value);
    }
  }

  public function match( Request $request ) {
    $parts= $request->uri();
    $p_size= count($parts);
    $s_size= count($this->segments);
    // if we have more parameters passed, as expected.
    if ( $p_size > $s_size ) {
      return false;
    }

    if( $p_size != 0 ) {
      for($i=0;$i<$s_size;$i++) {
        // access corresponding part.
        if(!isset($parts[$i])) continue;
        $segment= $this->segments[$i];
        $part   = $parts[$i];
        // if segment is not dynamic and segment name is not equal to current part without extension
        // eg. /foo defined while /bar requested :p
        if( !$segment->is_dynamic() && $segment->name () != $this->strip_ext($part) ) return false;
        // if a requirement is set on this segment and if it's not meet
        elseif( isset( $this->requirements[$segment->name()] )  &&
          !preg_match( $this->requirements[$segment->name()], $part )
        ) return false;
        // ready to merge then
        else $this->merges[$segment->name()] = $this->strip_ext($part);
      }
    }
    
    // merge request parameters
    $this->merge( $request );

    // load default values
    $this->defaults( $request );

    // validate 
    $this->validate( $request );

    Medick::dump('huh?');
    return true;
  }

  //
  // if 
  // -> c.bar is passed, c is returned :)
  // -> yahoo.html => yahoo
  //
  private function strip_ext($on) {
    if (false === strpos($on, '.html')) {
      $part = $on;
    } else {
      list($part)= explode('.', $on);
    }
    return $part;
  }

  public function create_controller() {

  }

}
