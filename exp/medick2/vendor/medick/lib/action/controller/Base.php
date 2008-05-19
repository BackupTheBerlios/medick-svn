<?php

// $Id: $

class ChainError extends Exception {  }

class ActionController extends Object {

  public $action;

  public $controller;

  protected $logger;

  protected $config;

  protected $request;

  protected $response;

  protected $session = null;

  protected $before_filters= '';

  protected $after_filters = '';

  protected $use_session = true;

  protected $use_layout  = true;

  // protected $use_etag    = false;

  protected $template = null;

  private $execution_chain = null;

  private $context;

  // XXX: throw an error if multiple render/redirects where called / execution_chain
  private $action_performed = false;

  // ---
  // final
  // ---

  final public function __construct( ContextManager $context ) {
    $this->context= $context;
    $this->logger= $context->logger();
    $this->config= $context->config();
  }

  // should return ActionView
  final public function process(Request $request, Response $response) {
    // create instance variables needed
    $this->__initialize($request, $response);

    // validate the action
    $this->__validate_action();

    // collect before filters now

    // execute those filters

    // execute the action
    $this->__execute_action();

    // render if we didn't do it yet
    if(false === $this->action_performed)
      $this->render();

    // collect after filters

    // execute after filters

    // Medick::dump( $request );
    return $response;
  }

  // ---
  // magick
  // ---

  // public function __set($name, $value) {
  //   $this->template->assign($name, $value);
  // }

  // ---
  // protected --> renders
  // ---

  protected function render($template_location= null, $status= null) {
    if(null === $template_location) {
      $template_location= $this->request->parameter('controller') . DIRECTORY_SEPARATOR . $this->request->parameter('action') . '.phtml';
    }
    return $this->render_file( $template_location, $status );
  }

  protected function render_file( $template_file, $status= null ) {
    
    // XXX: register flash

    if($this->use_layout) {
      // XXX: check view paths
      $layout= APP_PATH . 'app/views/layouts/' . $this->request->parameter('controller') . '.phtml';
      if(false === file_exists($layout)) {
        $this->logger->debug('will render without layout since cannot load `'.$layout.'`, no such file!');
        return $this->render_without_layout($template_file, $status);
      } else {
        // parse the template file first and create content_for_layout variable
        $this->template->assign('content_for_layout', $this->template->render( APP_PATH . 'app/views/' . $template_file));
        // continue to render the layout
        return $this->render_text($this->template->render($layout), $status);
      }
    } else {
      $this->logger->debug('rendering without layout.');
      return $this->render_without_layout($template_file, $status);
    }
  }

  protected function render_without_layout($file, $status) {
    return $this->render_text( $this->template->render( APP_PATH . 'app/views/' . $file), $status);
  }

  protected function render_text( $text, $status= null) {
    if( $this->action_performed ) 
      throw new Exception('Cannot render, action already performed.');
    
    // XXX: etag

    $this->response->content= $text;
    $this->response->setStatus($status);
    $this->action_performed= true;

    // XXX: remove flash
  }

  // ---
  // private
  // ---

  /* make assignments */
  private function __initialize(Request $request, Response $response) {
    // instance variables
    $this->request = $request;
    $this->response= $response;

    $this->execution_chain= new ExecutionChain( $this, $this->context );

    // do we need to register and start a session?
    $this->__register_session();

    // create the template now.
    $this->template= ActionView::load( $this->context, $this );

    // assign basic template variables.
    $this->template->assign('__controller', $this->request->parameter('controller'));
    $this->template->assign('__action', $this->request->parameter('action'));
    return true;
  }

  private function __register_session() {
    if($this->use_session === false || $this->config->property('web.session') === false) 
      return;
    // XXX: session container code should be written here
    if($session_container_class= $this->config->property('web.session.container')) {  }
    // then start the session
    $this->session= $this->request->session()->start();
  }

  private function __validate_action() {
    try {
      $this->execution_chain->validate_action( $this->request );
      return true;
    } catch(ChainError $err) {
      throw $err;
    }
  }

  private function __execute_action() {
    $this->execution_chain->exec_action();
  }

}

