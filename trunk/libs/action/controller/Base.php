<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
//   be used to endorse or promote products derived from this software without
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// $Id$
//
// ///////////////////////////////////////////////////////////////////////////////
// }}}

include_once('action/view/Base.php');
include_once('action/view/JSON.php');

/**
 * Base Class For User Application Controllers
 *
 * <h3>Controllers Basics</h3>
 *
 * Sample1:
 *
 * <code>
 *  class ProjectController extends ApplicationController {
 *     
 *    public function index() {
 *      $this->render_text("Hello Index!");
 *    }
 *
 *    public function hello() {
 *      $this->date= date('%Y-%m-%d');
 *      // similar with this:
 *      // $this->template->assign('date', date('%Y-%m-%d'));
 *    }
 *
 *  }
 * </code>
 * 
 * The class <em>ProjectController</em> is a Controller for your application.
 *
 * To hava a valid Controller you need to:
 *
 * 1. name your Controller class with <em>${Name}Controller</em>, eg: ProjectController, UserController, MainController
 *
 * 2. extend ApplicationController or ActionController
 *
 * 3. save the Controller class file as <em>${name}_controller.php</em> 
 * inside <em>app/controllers</em> folder, eg: app/controllers/project_controller.php
 *
 * Each application can have a base ApplicationController defined in <em>app/controllers/application.php</em>.
 * Using inheritance other Controllers can use methods defined in ApplicationController.
 *
 * In the above sample, <em>index</em> and <em>hello</em> methods represents an Action.
 * Using the medick Routing system, incoming requests are mapped to an action. 
 * In fact, every public method inside your Controllers are Actions that can be executed 
 * invoking the proper URL in the browser.
 *
 * To protect your data and to avoid strange behaviors in your application, keep any other method
 * that do not represents an Action as protected or private.
 *
 * Back to Sample1, the method <em>index</em> inside <em>ProjectController</em> class 
 * is mapped to a Request with this form:
 *
 * <pre>
 *  /project/index
 *  /project/index.html
 *  /project.html
 * </pre>
 *
 * while the <em>hello</em> action will be invoked when the Request URL is:
 *
 * <pre>
 *   /project/hello.html
 *   /project/hello
 * </pre>
 *
 * <h3>Building a Response, the View</h3>
 *
 * After the Action is executed, medick will build a Response object that later on will be 
 * passed to the user agent. This object is build using the rendering system.
 * 
 * By default, medick will try to load and parse - using a template engine system - 
 * a View located inside the folder app/views/CONTROLLER/ACTION.phtml.
 *
 * A View is the place where you should write client-side code or the place 
 * where you should use variables defined in the Controller.
 *
 * The default template engine system is a pure PHP implementation, this way you can use PHP syntax
 * for whatever operations you need without learning or debuging code that you are not familiar with it.
 * On medick users request, other systems could be integrated into the framework. 
 * And I'm thinking about Smarty or PHPTAL.
 *
 * Inside of an Action, the user can define <em>template variables</em>. A template variable is a
 * PHP variable that will be available in the Views.
 * To define such a variable, you can access <em>assign</em> method on the template object 
 * - witch is the recommended way to do it - or, you can use the PHP magick (already implemented):
 *
 * <code>
 *  $this->template->assing('variable_name', 'variable_value');
 * </code>
 * or:
 * <code>
 *  $this->variable_name = variable_value;
 * </code>
 * 
 * Later on, in the View, you can access the <em>variable_name</em> using <em>$variable_name</em> syntax.
 *
 * <h3>Anatomy of a medick Request</h3>
 *
 * Order of the invoked methods:
 * [Medick Framework]: instantiate --> load_models --> 
 * [User Controller]: __common --> before_filters --> action --> after_filters
 * 
 * <h4>Loading Models</h4>
 *
 * A Controller is the place where we indicate what Models we need. 
 * We do this by defining a <em>models</em> instance variable.
 *
 * Sample2:
 * <code>
 * class ProjectController extends ApplicationController {
 *      protected $models= array('project', 'user');
 *     // same as:
 *     // protected $models = 'project, user';
 *
 *     // lists available projects
 *     public function show() {
 *          $this->template->assign('projects', Project::find());
 *     }
 *
 * }
 * </code>
 *
 * The framework will know how to include the requested models and how to perform basic checks on them.
 *
 * <h4>Need a constructor?</h4>
 *
 * Since you might need some sort of contructor for your Controller, but defining one might lead to
 * confusions on the system, medick introduced a magick method: <em>__common</em> 
 * that it's invoked after the Controller is ready.
 * Not a real constructor, but the <em>__common</em> method can be used to perform all kind of initializations that
 * one needs.
 *
 * Sample3:
 * <code>
 * class ProjectController extends ApplicationController {
 *     private $base_project;
 *     protected $models = 'project, user';
 *
 *     protected function __common() {
 *          $this->base_project= Project::find('first');
 *     }
 *
 *     public function sub_projects() {
 *          $p= Project::find('all', array('conditions'=>"parent_id=" . $this->base_project->id));
 *          $this->template->assign('projects', $p);
 *     }
 * }
 * </code>
 *
 * <h4>ActionController filters</h4>
 * 
 * <h3>Available Objects</h3>
 * 
 * <h4>Accessing the Request parameters</h4>
 *
 * <h4>Session</h4>
 *
 * <h4>Respose tunning</h4>
 *
 * <h4>Using Logger</h4>
 *
 * <h4>Accessing custom confguration</h4>
 *
 * <h3>Pre-defined template variables</h3>
 *
 * ActionController also defines some magic medick template variables. We will use <b>__</b> to mark them.
 * Predefined medick variables available in templates:
 * <ul>
 *  <li>__base: the document root</li>
 *  <li>__server_name: this server name</li>
 *  <li>__controller: incoming controller</li>
 *  <li>__action: current action</li>
 *  <li>__version: medick version</li>
 * </ul>
 * 
 * <h3>Other features</h3>
 *
 * <h4>flash!</h4> 
 *  
 * method can keep anything in session for the next controller, after that the flash container is discarded
 * 
 * <h4>layouts</h4>
 * 
 * unified look and feel for your application using layouts
 *
 * <h4>redirects</h4>
 *
 * <h3>Missing features</h3>
 *
 * 
 *
 * 
 * @package medick.action.controller
 * @author Aurelian Oancea
 */
class ActionController extends Object {

    /** @var Logger
        logger instance */
    protected $logger;

    /** @var Request
        current request */
    protected $request;

    /** @var array
        Request parameters */
    protected $params;

    /** @var Response
        Response that we are building */
    protected $response;

    /** @var array
        request heders */
    protected $headers;

    /** @var Session
        current request session */
    protected $session;

    /** @var array
        values for template class */
    private $assigns;

    /** @var string
        Default location for template files*/
    private $template_root;

    /** @var string
        application path*/
    private $app_path;

    /** @var string
        the layout to use */
    protected $use_layout= true;

    /** @var bool  
        etag flag, set to true to use the Etag caching, false to disable-it */
    protected $use_etag= false;

    /** @var ActiveViewBase
        Template Engine */
    protected $template;

    /** @var array
        before_filters array */
    protected $before_filters= array();

    /** @var array
        after_filters array */
    protected $after_filters= array();    

    /** @var array
        list of models */
    protected $models= array();

    /** @var bool
        Flag to indicate that the current action was performed.*/
    private $action_performed= FALSE;

    /** @var Configurator
        configurator instance */
    protected $config;

    /** @var Injector
        the injector. */
    protected $injector;

    /**
     * Process this Request when an exception occured
     *
     * @param Request $request
     * @param Response $response
     * @param Exception $exception
     * @return Response
     */
    public static function process_with_exception( Request $request, Response $response, Exception $exception ) {
        $body = $response->getContent();
        if(ob_get_length()) {
            ob_end_clean();
        }
        $template = ActionView::factory('php');
        $template->assign('error', $exception);
        $template->assign('request', $request);
        $template->assign('response', $response);
        $template->assign('body', $body);
        $text= $template->render_file(MEDICK_PATH . '/libs/action/controller/templates/error.phtml');
        $response->setStatus(HTTPResponse::SC_INTERNAL_SERVER_ERROR);
        $response->setContent($text);
        return $response;
    }

    /**
     * Will process the request returning the resulting response
     * 
     * @param Request request, the request
     * @param Response response, the response
     * @return Response
     */
    public final function process(Request $request, Response $response) {
        $this->instantiate($request, $response);
        $this->load_models();
        $this->__common();
        $this->execute_before_filters();
        $this->perform_action( $request->getParameter('action') );
        $this->execute_after_filters();
        return $response;
    }

    // {{{ callbacks
    protected function __common() {  }
    // }}}

    // {{{ renders.

    /**
     * It renders the template name witch can be the name of the curent action.
     * <code>
     *  class news_controller extends ActionController {
     *      public function add {
     *          // grab some data and pass it on the template
     *          $this->template->bazaas = Bazaas::find();
     *          $this->render(); // will load the template /app/views/news/add.phtml
     *          // the template assgnment will not work anymore
     *          $this->template->item = 'foo';
     *      }
     *  }
     * </code>
     * @param string template_name, [optional], the template name, default is null, the curent action.
     * @param HTTPResponse::SC_*, status, [optional] status code, default is 200 OK
     * @return void
     */
    protected function render($template_name = NULL, $status = NULL) {
        if ($template_name === NULL) {
            $template_name = $this->params['action'];
        }
        $this->render_file($this->template_root . $template_name . '.phtml', $status);
    }

    /**
     * It renders the template file.
     *
     * This method is usefull when you don`t want to use the default template_root
     * Special helper file is included.
     * Magic __layout.phtml is loaded if exists.
     *
     * @param string, template_file location of the template file, default NULL
     * @param Response::SC_*, status, [optional] status code, default is 200 OK
     * @throws FileNotFoundException if the template file don`t exist on the specified location.
     * @return void
     */
    protected function render_file($template_file, $status = NULL) {
        if (!is_file($template_file)) {
            throw new FileNotFoundException ('Cannot render unexistent template file:' . $template_file);
        }
        // load helper
        $this->injector->inject('helper', $this->params['controller']);
        // register flash
        $this->register_flash();
        
        if ($this->use_layout) {
            $layout= $this->use_layout === TRUE ? $this->params['controller'] : $this->use_layout;
            $layout_file= $this->injector->getPath('layouts') . $layout . '.phtml';
            $this->logger->debug('[Medick] >> Using layout: ' . 
                str_replace( $this->config->getApplicationPath(), '${'.$this->config->getApplicationName().'}', $layout_file));
            if (!is_file($layout_file)) {
                return $this->render_without_layout($template_file, $status);
            } else {
                $this->template->content_for_layout= $this->template->render_file($template_file);
                return $this->render_text($this->template->render_file($layout_file), $status);
            }
        } else {
            return $this->render_without_layout($template_file, $status);
        }
    }

    /**
     * Renders a partial
     *
     * @param string
     * @param string
     * @param HTTPResponse::SC_*
     */ 
    protected function render_partial($partial, $controller=NULL, $status=NULL) {
        $this->register_flash();
        if ($controller === NULL) {
            $location = $this->template_root;
        } else {
            $location = $this->injector->getPath('views') . $controller . DIRECTORY_SEPARATOR;
        }
        return $this->render_without_layout($location . '_' . $partial . '.phtml', $status);
    }

    protected function render_without_layout($template_file, $status) {
        $this->logger->debug('Rendering without layout...');
        return $this->render_text($this->template->render_file($template_file), $status);
    }

    protected function render_json($text, $status = NULL) {
        $this->response->setContentType('application/json');
        $text= JSON::encode($text);
        $this->response->setHeader('X-JSON', '('.$text.')');
        $this->render_text($text, $status);
    }

    /**
     * Will render some text.
     * 
     * Is the _base_ method for render_file.
     * 
     * This method is useful when you want to output some text without using the template engine
     * 
     * In case the action was already performed we will silently exit,
     * otherwise, we set the response status and body and
     * switch the action_performed flag to <i>TRUE</i>
     * 
     * @param string text [optional]the text you want to send, default is an empty string
     * @param Response::SC_* status, [optional] status code, default is 200 OK
     */
    protected function render_text($text = '', $status = NULL) {
        if ($this->action_performed) {
            $this->logger->warn('[Medick] >> Action already performed...');
            return;
        }
        
        $status = $status === NULL ? HTTPResponse::SC_OK : $status;

        // add ETag header
        if( $this->use_etag && $status == HTTPResponse::SC_OK && strlen($text) > 0 ) {
            $this->set_etag_headers( $text );
            if( $this->request->getHeader('If-None-Match') == md5($text) ) {
                $this->logger->debug( '[Medick] >> Got response from browser cache (code=304, body="").' );
                $this->_perform( HTTPResponse::SC_NOT_MODIFIED, '' );
            } else {
                $this->_perform($status, $text);
            }
        }  else {
            $this->_perform($status, $text);
        }

        $this->action_performed= TRUE;
        $this->logger->debug( '[Medick] >> Action performed.' );

        if ($this->session->hasValue('flash')) {
            $this->session->removeValue('flash');
        }
    }
  
    // move to response
    private function _perform($status, $text) {
      $this->response->setStatus($status);
      $this->response->setContent($text);
    }

    private function set_etag_headers( $text ) {
      $this->response->setHeader('ETag', md5($text));
      $this->logger->debug( sprintf('[Medick] >> ETag set to %s.', md5($text)) );
      // get around PHP session
      $this->response->setHeader('Cache-Control', null, false);
      $this->response->setHeader('Expires', null, false);
      $this->response->setHeader('Pragma', null, false);
    }

    // }}}

    private function register_flash() {
        if ($this->session->hasValue('flash')) {
            $this->template->assign('flash', $this->session->getValue('flash'));
        } else {
            $this->template->assign('flash', NULL);
        }
    }

    protected function flash($name, $value) {
        if ($this->session->hasValue('flash')) {
            $this->session->putValue('flash', array_merge($this->session->getValue('flash'), array($name=>$value)));
        } else {
            $this->session->putValue('flash', array($name=>$value));
        }
    }

    /**
     * Act as an internal constructor.
     * 
     * @param Request request, the request
     * @param Response response, the response
     */
    private function instantiate(Request $request, Response $response) {
        // class memebers
        $this->request  = $request;
        $this->response = $response;
        $this->params   = $request->getParameters();
        $this->logger   = Registry::get('__logger');
        $this->injector = Registry::get('__injector');
        $this->config   = Registry::get('__configurator');
        $this->session  = $request->getSession();
        $this->app_path = $this->injector->getPath('__base');
        $this->template_root = $this->injector->getPath('views') . $this->params['controller'] . DIRECTORY_SEPARATOR;
        $this->template = ActionView::factory('php');
        
        // register session container if any
        // TODO: this should be moved elsewhere
        if ($this->config->getWebContext()->session !== NULL && 
            $this->config->getWebContext()->session->container !== NULL
        ) {
            // container location.
            $c_location= str_replace('.', DIRECTORY_SEPARATOR, 
                  (string)$this->config->getWebContext()->session->container) . '.php';
            include_once($c_location);
            // container class name.
            $e= explode('.',(string)$this->config->getWebContext()->session->container);
            // reflect on container.
            $container= new ReflectionClass(end($e));
            if ($container->implementsInterface('ISessionContainer')) {
                $this->session->setContainer($container->newInstance());
            }
        }
       
        // predefined variables:
        // TODO: check if we have a / at the end, if not, add one
        $this->template->assign('__base',       (string)$this->config->getWebContext()->document_root);
        $this->template->assign('__server',     (string)$this->config->getWebContext()->server_name);
        $this->template->assign('__controller', $this->params['controller']);
        $this->template->assign('__version',    Medick::getVersion());
        $this->template->assign('__self', $this->__base . $this->request->getRequestUri());
        $this->logger->debug($this->request->toString());
    }

    /**
     * Shortcut for template assigns
     */
    public function __set($name, $value) {
        // if (in_array($name, array_keys(get_object_vars($this)))) {
        //     throw new MedickException('Cannot set ' . $name . ' as template variable');
        // }
        $this->template->assign($name, $value);
    }

    public function __get($name) {
        return $this->template->$name;
    }

    protected function sendFile($location, $options = array()) {
        // if(!is_file($location)) {
            throw new MedickException('Method ' . __METHOD__ . ' Not Implemented!');
        // }
        // $options['length'] =   File::size($location);
        // $options['filename'] = File::basename($location);
    }

    // {{{ redirects

    /**
     * Redirects the current Response
     *
     * This changes the flag of action performed to TRUE
     * @param string action to redirect to
     * @param string controller defaults to NULL, the current controller
     * @param array params, additional parameters to pass with this redirect.
     */
    protected function redirect_to($action, $controller= NULL, $params = array(), $ext='html') {
        if ($this->action_performed) return;
        if ($controller === NULL) {
            $controller= $this->params['controller'];
        }
        
        $rewrite = strtolower($this->config->getWebContext()->rewrite);
        
        $redirect_to= $this->config->getWebContext()->server_name . $this->config->getWebContext()->document_root . '/';
        
        if ($rewrite == 'false' || $rewrite == 'off' || $rewrite == '0') {
            $redirect_to .= 'index.php/';
        }
        $redirect_to .= $controller;
        if ($action !== NULL) {
            $redirect_to .= '/' . $action;
        }
        if (count($params)) {
            $redirect_to .= '/' . implode('/', $params);
        }
        $redirect_to .= '.' . $ext;
        
        $this->logger->debug('[Medick] >> Redirecting to: ' . $redirect_to);
        $this->response->redirect($redirect_to);
        $this->action_performed = TRUE;
    }

    // XXX: not done.
    // redirects to a know path (eg. /images/pic.jpg)
    protected function redirect_to_path($path) {
        $this->logger->debug('[Medick] >> Redirecting to: ' . $path);
        $this->response->redirect($this->config->getWebContext()->server_name . $this->config->getWebContext()->document_root . '/' . $path);
        $this->action_performed = TRUE;
    }

    // XXX: not done.
    protected function redirect($url, $message = '', $timeout = 0, $template = NULL) {
        throw new MedickException('Method: ' . __METHOD__ . ' not implemented!');
    }

    // }}}

    /**
     * Performs the action
     * 
     * @param string, action_name, the action to perform
     * @todo still to refactor.
     * @todo do not try to create the ``index" method, just throw an error.
     * @throws RoutingException if the action cannot be invoked and the index method is not defined
     */
    private function perform_action($action_name) {
        $forbidden_actions = array('process', '__construct', '__destruct', '__common');
        $action= $this->createMethod($action_name);

        if (
            is_null($action_name) ||
            in_array($action_name, $forbidden_actions) ||
            !$action ||
            $action->isStatic() )
        {
            $action_name = 'index';
            $action= $this->createMethod($action_name);
            if (!$action || $action->isStatic()) {
                throw new RoutingException('Cannot invoke default action, `index` for this Route!',
                    'Method named `index` is not defined in class: `' . $this->getClassName() . '`');
            }
        }
        $this->params['action'] = strtolower($action_name);
        $this->template->assign('__action', $this->params['action']);
        // invoke the action.
        $action->invoke($this);
        if ($this->action_performed) return;
        $this->render();
    }

    /**
     * It executes before filters
     *
     * Loop on before_filter array, invoking the method before the action
     * <code>
     *  class NewsController extends ActionControllerBase {
     *    // use protected on before_filter member
     *    protected before_filter = array('authenticate');
     *    // an action
     *    public function index() {
     *      $this->news= News::find_all();
     *    }
     *    // Notes: 1) use protected for internal filters
     *    // 2) a filter must return void, in case of a failure,
     *    // use the redirect method.
     *    protected function authenticate() {
     *      // authentication code here
     *    }
     *  }
     * </code>
     * @throws MedickException if the definition of the before filter is wrong
     */
    private function execute_before_filters() {
        $this->execute_filters( $this->before_filters );
    }
    
    private function execute_after_filters() {
        $this->execute_filters( $this->after_filters );
    }

    private function execute_filters( $filters ) {
        if(!is_array($filters)) $filters = explode(',', $filters);
        foreach($filters as $name) {
            $name= trim($name);
            // try to create the method
            if(!$filter= $this->createMethod( $name )) {
                $this->logger->warn(sprintf('[Medick] >> Cannot laod filter `%s::%s`, call to undefined method.',
                    $this->getClassName(), $name));
                continue;                
            }
            // a filter should be declared as protected.
            if (!$filter->isProtected()) {
                $this->logger->warn('[Medick] >> Your filter, `'. $name . '` is not declared as a
                    protected method for class `' . $this->getClassName() .'`, so it cannot be executed.');
                continue;
            }
            $this->logger->debug('[Medick] >> Executing filter: `' . $name. '`.');
            $this->$name();
            // $this->logger->debug('[Medick] >> Filter ' . $name . ' executed.');
        }
    }

    /**
     * Injects model names into ActiveRecordBase by using the ModelInjector.
     */
    private function load_models() {
        if (!is_array($this->models)) {
            $this->models= explode(',',$this->models);
        }
        foreach ($this->models as $model) {
            if ( trim($model) != '' ) {
                $this->injector->inject('model', trim($model));
            }
        }
    }

    /**
     * By using the php Reflection API we create
     * in a safty way the method with the name $method_name on this object
     *
     * This method is used to perform the actions given by before/pre filters
     * and also when we perform the action
     * @todo can we move this to the Object class?
     * @param string method_name, the method.
     *                            NOTE: We force the name to be on lower case.
     * @return RelfectionMethod or FALSE in case of failure.
     */
    private function createMethod($method_name) {
        try {
            return new ReflectionMethod($this, strtolower($method_name));
        } catch (ReflectionException $rEx) {
            // $this->logger->info($rEx->getMessage());
            return FALSE;
        }
    }
}
