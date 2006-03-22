<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Oancea Aurelian nor the names of his contributors may
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

/**
 * Base Class For Our Application Controllers
 *
 * Controller Sample
 * <code>
 *  class ProjectController extends ApplicationController {
 *    public function index() {
 *      $this->render_text("Hello Index!");
 *    }
 *  }
 * </code>
 * Valid Controllers will be keept inside the folder app/controllers and will extend
 * ApplicationController or ActionController.
 *
 * Incoming actions should be declared as public methods inside the ActionController.
 * Keep your helper methods as protected or private since one could somehow find 
 * the URL to invoke them.
 *
 * Inside ApplicationController (usualy is the base class for your application controllers)
 * a method named <i>__common</i> is always invoked before the action itself. You can use this
 * method for assing template variables common to all your templates (like a menu with items from database).
 *
 * ActionController also defines some magic medick template variables. We will use <i>__</i> to mark them.
 * Predefined medick variables available in templates:
 * <ul>
 *  <li>__base: the document root</li>
 *  <li>__server_name: this server name</li>
 *  <li>__controller: incoming controller</li>
 * </ul>
 * 
 * Other features:
 * <ul>
 *  <li>flash() method can keep anything in session for the next controller, after that the flash container is discarded</li>
 *  <li>unified look and feel for your application by using layouts</li>
 *  <li>straight access to ActionView</li>
 *  <li>filters (this is still a work in progress)</li>
 *  <li>logging capabilities</li>
 * </ul>
 * 
 * @package medick.action.controller
 * @author Oancea Aurelian
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
    protected $use_layout= TRUE;

    /** @var ActiveViewBase
        Template Engine */
    protected $template;

    /** @var array
        before_filters array */
    protected $before_filter= array();

    /** @var array
        list of models */
    protected $models= array();

    /** @var bool
        Flag to indicate that the current action was performed.*/
    private $action_performed= FALSE;

    /** @var Configurator
        configurator instance */
    private $config;

    /** @var Injector
        the injector. */
    private $injector;

    /**
     * Process this Request when an exception occured
     *
     * @param Request $request
     * @param Response $response
     * @param Exception $exception
     * @return Response
     */
    public static function process_with_exception(
                                                   Request $request,
                                                   Response $response,
                                                   Exception $exception) {
        if(ob_get_length()) {
            ob_end_clean();
        }
        $template = ActionView::factory('php');
        $template->error= $exception;
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
    public function process(Request $request, Response $response) {
        $this->instantiate($request, $response);
        $this->add_models();
        $this->add_before_filters();
        $this->perform_action($request->getParameter('action'));
        return $response;
    }

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
        if (is_null($template_name)) $template_name = $this->params['action'];
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

        try {
            $this->injector->inject('helper', $this->params['controller']);
        } catch (FileNotFoundException $fnfEx) {
            $this->logger->info(
                'skipping helper: '
                . $this->injector->getPath('helpers')
                . '_' . $this->params['controller'] . ' ' . $fnfEx->getMessage());
        }
        $this->register_flash();
        if ($this->use_layout) {
            $layout= $this->use_layout === TRUE ? $this->params['controller'] : $this->use_layout;
            $layout_file= $this->injector->getPath('layouts') . $layout . '.phtml';
            $this->logger->debug('Layout: ' . $layout_file);
            if (!is_file($layout_file)) {
                $this->logger->debug('...failed.');
                return $this->render_without_layout($template_file, $status);
            } else {
                $this->template->content_for_layout= $this->template->render_file($template_file);
                $this->logger->debug('...done.');
                return $this->render_text($this->template->render_file($layout_file), $status);
            }
        } else {
            return $this->render_without_layout($template_file, $status);
        }
    }

    protected function render_partial($partial, $controller=NULL, $status=NULL) {
        $this->register_flash();
        if (is_null($controller)) $location = $this->template_root;
        else $location = $this->injector->getPath('views') . $controller . DIRECTORY_SEPARATOR;
        // $this->logger->debug('Partial: ' . $location . '_' . $partial . '.phtml');
        return $this->render_without_layout($location . '_' . $partial . '.phtml', $status);
    }

    protected function render_without_layout($template_file, $status) {
        $this->logger->debug('Rendering without layout...');
        return $this->render_text($this->template->render_file($template_file), $status);
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
            $this->logger->info('Action already performed...');
            return;
        }
        $status = $status === NULL ? HTTPResponse::SC_OK : $status;
        $this->response->setStatus($status);
        $this->response->setContent($text);
        $this->action_performed = TRUE;
        $this->logger->debug('Action performed.');

        if ($this->session->hasValue('flash')) {
            $this->session->removeValue('flash');
        }
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
        $this->session->putValue('flash', array($name=>$value));
    }

    /**
     * Act as an internal constructor.
     * 
     * @param Request request, the request
     * @param Response response, the response
     */
    private function instantiate(Request $request, Response $response) {
        $this->request  = $request;
        $this->response = $response;
        $this->session  = $request->getSession();
        $this->session->start();
        $this->params   = $request->getParameters();

        $this->logger   = Registry::get('__logger');
        $this->injector = Registry::get('__injector');
        $this->config   = Registry::get('__configurator');

        $this->app_path      = $this->injector->getPath('__base');
        $this->template_root = $this->injector->getPath('views') . $this->params['controller'] . DIRECTORY_SEPARATOR;

        $this->template = ActionView::factory('php');
        // predefined variables:
        // TODO: check if we have a / at the end, if not, add one
        
        $this->template->assign('__base', $this->config->getWebContext()->document_root);
        $this->template->assign('__server', (string)$this->config->getWebContext()->server_name);
        
        $this->template->assign('__controller', $this->params['controller']);
        $this->template->assign('__version', Medick::getVersion());
        $this->logger->debug($this->request->toString());
    }

    /**
     * Shortcut for template assigns
     */
    public function __set($name, $value) {
        $this->template->assign($name, $value);
    }

    public function __get($name) {
        return $this->template->$name;
    }

    // XXX: not-done!
    protected function sendFile($location, $options = array()) {
        if(!is_file($location)) {
            throw new MedickException("File not found...");
        }
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
    protected function redirect_to($action, $controller= NULL, $params = array()) {
        // get the curent controller, if NULL is passed.
        if (is_null($controller)) {
            $controller= $this->params['controller'];
        }
        $redirect_to= $this->config->getWebContext()->server_name . $this->config->getWebContext()->document_root . '/';
        $rewrite = strtolower($this->config->getWebContext()->rewrite);
        if ($rewrite == 'false' || $rewrite == 'off' || $rewrite == '0') {
            $redirect_to .= 'index.php/';
        }
        $redirect_to .= $controller . '/' . $action;
        if (count($params)) $redirect_to .= '/' . implode('/', $params);
        $redirect_to .= '.html';
        $this->logger->debug('Redirecting to: ' . $redirect_to);
        $this->response->redirect($redirect_to);
        $this->action_performed = TRUE;
    }

    // XXX: not done.
    // redirects to a know path (eg. /images/pic.jpg)
    protected function redirect_to_path($path) {   }

    // XXX: not done.
    protected function redirect($url, $message = '', $timeout = 0, $template = NULL) {     }

    // }}}

    /**
     * Performs the action
     *
     * The magic __common method is also invoked just before the action to perform
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
                throw new RoutingException(
                    'Cannot invoke default action, ``index" for this Route!',
                    'Method named ``index" is not defined in class: ' . $this->getClassName()
                );
            }
        }
        $this->params['action'] = strtolower($action_name);
        // $this->logger->debug('Action:: ' . strtolower($action_name));
        // quickly load the common magick method.
        if ($_common= $this->createMethod('__common')) {
            $_common->invoke($this);
        }
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
     *      return News::find_all();
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
    private function add_before_filters() {
        if (!is_array($this->before_filter)) {
            throw new MedickException(
                $this->getClassName() . '->\$before_filter should be an array
                    of strings, each string representing a method name');
        }
        foreach($this->before_filter as $filter_name) {
            if (!$filter= $this->createMethod($filter_name)) {
                $this->logger->info(
                    'Could not create filter: ``'.$filter_name.'", skipping...');
                continue;
            }
            // a filter should be declared as protected.
            if (!$filter->isProtected()) {
                throw new MedickException(
                    'Your filter,``'. $filter_name . '" is declared as a
                        public method of class ``' . $this->getClassName() .'" !');
            }
            // can we use invoke?
            $this->$filter_name();
        }
    }

    /**
     * Injects model names into ActiveRecordBase by using the ModelInjector.
     */
    private function add_models() {
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
            $this->logger->info($rEx->getMessage());
            return FALSE;
        }
    }
}

