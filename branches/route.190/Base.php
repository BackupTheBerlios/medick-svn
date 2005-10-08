<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice, 
//   this list of conditions and the following disclaimer. 
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation 
//   and/or other materials provided with the distribution. 
//   * Neither the name of locknet.ro nor the names of its contributors may 
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

/**
 * @package locknet7.action.controller
 */
    
include_once('action/controller/Injector.php');
include_once('action/view/Base.php');
 
/**
 * Base Class For Our Application Controllers
 */
class ActionControllerBase extends Object {
    
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
    protected $assigns;
    
    /** @var string 
        Default location for template files*/
	protected $template_root;
    
	/** @var string 
        application path*/
	protected $app_path;
    
    /** @var ActiveViewBase 
        Template Engine */
	protected $template;
    
    /** @var bool 
        Flag to indicate that the current action was performed.*/
    private $action_performed = FALSE;
    
    /** @var Configurator 
        configurator instance */
    private $config;
    
    /**
     * Will process the request returning the resulting response
     * @param Request request, the request
     * @param Response response, the response
     * @return Response
     */
    public function process(Request $request, Response $response) {
        $this->instantiate($request, $response);
        $this->add_before_filters();
        $this->add_models();
        $this->perform_action($request->getParam('action'));
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
     * @param Response::SC_*, status, [optional] status code, default is 200 OK
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
     * @throws Exception if the template file don`t exist on the specified location.
     * @return void
     */
    protected function render_file($template_file, $status = NULL) {
        if (!is_file($template_file)) {
            throw new Exception ('Cannot render unexistent template file:' . $template_file);
        }
        $helper_location = 
            $this->app_path . 'helpers' . DIRECTORY_SEPARATOR . $this->params['controller'] . '_helper.php';
        if (is_file($helper_location)) {
            include_once($helper_location);
        }
        
        // {{{ hook RouteParams here.
        $hij= array();
        $route= Registry::get('__map')->getCurrentRoute();
        foreach($route->getParams() as $param) {
            $hij[$param->getName()]['message'] = 'foo';
            $hij[$param->getName()]['value']   = $param->getValue();
        }
        $this->template->__param = $hij;
        // }}}
        
        if (is_file($_layout=$this->app_path . 'views' . DIRECTORY_SEPARATOR .  '__layout.phtml')) {
            $this->logger->debug('Found magick __layout description file...');
            $this->template->__content= 
                $this->params['controller'] . DIRECTORY_SEPARATOR . $this->params['action'] . '.phtml';
            $this->render_text($this->template->render_file($_layout), $status);
        } else {
    	   $this->render_text($this->template->render_file($template_file), $status);
        }
	}
	
    /**
     * Will render some text.
     * Is the _base_ method for render_file
     * This method is useful when you want to output some text without using the template engine
     * In case the action was already performed we will silently exit, 
     * otherwise, we set the response status and body and 
     * switch the <code>action_performed</code> flag to <code>TRUE</code>
     * @param string text, [optional]the text you want to send, default is an empty string
     * @param Response::SC_*, status, [optional] status code, default is 200 OK
     * @return void
     */
    protected function render_text($text = '', $status = NULL) {
        if ($this->action_performed) {
            $this->logger->info('Action already performed...');
            return;
        }
        if (is_null($status)) $status = Response::SC_OK;
		$this->response->setStatus($status);
        $this->response->setContent($text);
        $this->action_performed = TRUE;
	}
	
	// }}}

    /**
     * Act as an internal constructor.
     * @param Request request, the request
     * @param Response response, the response
     * @return void
     */
    private function instantiate(Request $request, Response $response) {
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = Registry::get('__logger');
        $this->session  = $request->getSession();
        $this->params   = $request->getParams();
        $this->config   = Registry::get('__configurator');
        $this->app_path = $this->config->getProperty('application_path') . DIRECTORY_SEPARATOR;
        $this->template_root = 
            $this->app_path . 'views' . DIRECTORY_SEPARATOR . $this->params['controller'] . DIRECTORY_SEPARATOR;
		$this->template = ActionViewBase::factory();
    }

    // XXX: not-done!
    protected function sendFile($location, $options = array()) {
        if(!is_file($location)) {
            throw new MedickException("File not found...");
        }
        // $options['length'] = File->size($location);
        // $options['filename'] = File->basename($location);
    }

    // {{{ redirects
    
    // XXX: not done.
    protected function redirect_to($action, $params = array(), $controller = NULL) {
        // get the curent controller, if NULL is passed.
        if (is_null($controller)) $controller= $this->params['controller'];
        
        if ($this->config->getProperty('rewrite')) {
            $this->response->redirect(
                $this->config->getProperty('server_name') . $this->config->getProperty('document_root') . 
                $controller . '/' . $action . '.html');
        } else {
            // rewrite-off
            $this->response->redirect(
                $this->config->getProperty('server_name') . 
                $this->config->getProperty('document_root') . 
                '/index.php?controller=' . $controller . '&action=' . $action
            );
        }
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
     * Also, the magic __common method is invoked.
     * @param string, action_name, the action to perform
     * TODO: still to refactor.
     */
    private function perform_action($action_name) {
        $forbidden_actions = array('process', '__construct', '__destruct');
        
        if( (is_null($action_name)) OR (in_array($action_name, $forbidden_actions)) ) {
            $action_name = $this->config->getDefaultRoute()->action ? $this->config->getDefaultRoute()->action : 'index';
            $action = $this->createMethod($action_name);
            if (!$action OR $action->isStatic()) throw new Exception('Cannot perform default action: ' . $action_name);
        } else {
            $action = $this->createMethod($action_name);
            if (!$action OR $action->isStatic()) {
                $action_name = $this->config->getDefaultRoute()->action ? $this->config->getDefaultRoute()->action : 'index';
                $this->perform_action($action_name);
                $this->action_performed = TRUE;
                return;
            }
        }
        $this->params['action'] = strtolower($action_name);
        $this->logger->debug('Incoming action:: ' . strtolower($action_name));
        $action->invoke($this);
        if ($this->action_performed) return;
        if ($_common= $this->createMethod('__common')) {
            $_common->invoke($this);
        }
        $this->render();
    }
    
    /**
     * It executes before filters
     *
     * Loop on before_filter array, invoking the method before the action
     * <code>
     *      class NewsController extends ActionControllerBase {
     *          // use protected on before_filter member
     *          protected before_filter = array('authenticate');
     *          // an action
     *          public function index() {
     *              return News::find_all();
     *          }
     *          // Notes: 1) use protected for internal filters
     *          // 2) a filter must return void, in case of a failure, use the redirect method.
     *          protected function authenticate() {
     *              // authentication code here
     *          }
     *      }
     * </code>
     * @access private
     */
    private function add_before_filters() {
        if (isset($this->before_filter)) {
            foreach($this->before_filter AS $filter_name) {
                $filter = $this->createMethod($filter_name);
                if (!$filter->isProtected()) throw new MedickException('Your filter is declared as public!');
                $this->$filter_name();
                // $filter->invoke($this); will work only for public methods.
            }
        }
    }

	/**
	 * Injects model names into ActiveRecordBase by using the ModelInjector.
	 * TODO: table inheritance ?
     * TODO: can we hook a Registry here?
	 */
    private function add_models() {
        if (isset($this->model)) {
            $this->logger->debug("We have Models...");
            foreach ($this->model AS $model) {
                $this->logger->debug('Injecting Model:: ' . $model);
                Injector::inject($model);
            }
            Injector::prepareARBase();
        }
    }
    
    /** 
     * By using the php Reflection API we create 
     * in a safty way the method with the name $method_name on this object
     * 
     * This method is used to perform the actions given by before/pre filters
     * and also when we perform the action
     * TODO: can we move this to the Object class?
     * @param string method_name, the method.
     * 							  NOTE: We force the name to be on lower case.
     * @return RelfectionMethod or FALSE in case of failure. 
     */ 
    private function createMethod($method_name) {
        try {
            return new ReflectionMethod($this, strtolower($method_name));
        } catch (ReflectionException $rEx) {
            $this->logger->debug($rEx->getMessage());
            return FALSE;
        }
    }
}

