<?php

class ActionChain implements ArrayAccess, Iterator, Countable {

  private $chain;

  private $index;

  public function __construct(/*ActionController $action_controller*/) {
    $this->chain= array();
    $this->index= 0;
  }

  public function push( $value ) {
    $this[sizeof($this)]= $value;
  }

  public function prepend( $value ) {
    array_unshift($this->chain, $value);
  }

  public function offsetExists($offset) {
    return isset( $this->chain[$offset] );
  }

  public function offsetGet($offset) {
    return $this->chain[$offset];
  }

  public function offsetSet($offset, $value) {
    // if(false === is_int($offset)) throw new Exception('use only numrical values!');
    $this->chain[$offset]= $value;
  }

  public function offsetUnset($offset) {
    unset($this->chain[$offset]);
  }

  public function current() {
    // return $this->chain[$this->index];
    return $this[$this->index];
  }

  public function next() {
    $this->index++;
    // return $this->chain[$this->index];
    // return $this->current();
  }

  public function key() {
    return $this->index;
  }

  public function valid() {
    return $this[$this->index];
  }

  public function rewind() {
    $this->index--;
  }

  public function count() {
    return sizeof($this->chain);
  }

}

class ActionController extends Object {

  // protected $logger;
  // protected $config;

  protected $request;

  protected $response;

  protected $context;

  private $chain;

  final public function __construct(ContextManager $context) {
    $this->context= $context;
    // $this->logger= $context->logger();
    // $this->config= $context->config();
    $this->chain= new ActionChain();
  }

  // should return ActionView
  final public function process(Request $request, Response $response) {
    $this->request= $request;
    $this->response= $response;

    $this->chain->push( 'foo' );
    $this->chain->push( 'bar' );

    $this->chain->prepend( 'baz' );

    Medick::dump( $this->chain );

    // xxx: before.

    // xxx: after.

    Medick::dump($request);
  }

}

