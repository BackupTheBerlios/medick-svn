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

// namespace ActionController {

/**
 * @package locknet7.action.controller
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
     * Process this Request
     *
     * @param Request $request
     * @param Response $response
     * @param Exception $exception
     * @return Response
     */
    public static function process_with_exception(
                                                    Request $request,
                                                    Response $response,
                                                    Exception $exception)
    {
        if(ob_get_length()) {
            ob_end_clean();
        }
        $template = new ActionViewBase();
        // $template = new ActionView:::Base();
        $template->error= $exception;
        $text= $template->render_file(MEDICK_PATH . '/libs/action/controller/templates/error.phtml');
        $status = Response::SC_INTERNAL_SERVER_ERROR;
        $response->setStatus($status);
        $response->setContent($text);
        return $response;
    }

    /**
     * Will process the request returning the resulting response
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

        if ($this->use_layout) {
            $layout= $this->use_layout === TRUE ? $this->params['controller'] : $this->use_layout;
            $layout_file= $this->injector->getPath('layouts') . $layout . '.phtml';
            $this->logger->debug('Trying to use layout: ' . $layout_file);
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

        /*
        // {{{ hook RouteParams here.
        $hij= array();
        $route= Registry::get('__map')->getCurrentRoute();
        foreach($route->getParams() as $param) {
            $hij[$param->getName()]['message'] = 'foo';
            $hij[$param->getName()]['value']   = $param->getValue();
        }
        $this->template->__param = $hij;
        // }}}
        */

    }

    protected function render_without_layout($template_file, $status) {
        $this->logger->debug('Rendering without layout...');
        return $this->render_text($this->template->render_file($template_file), $status);
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
        if (is_null($status)) {
            $status = Response::SC_OK;
        }
        $this->response->setStatus($status);
        $this->response->setContent($text);
        $this->action_performed = TRUE;
        $this->logger->debug('Action performed.');
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
        $this->session  = $request->getSession();
        $this->session->start();
        $this->params   = $request->getParameters();

        $this->logger   = Registry::get('__logger');
        $this->injector = Registry::get('__injector');
        $this->config   = Registry::get('__configurator');

        $this->app_path      = $this->injector->getPath('__base');
        $this->template_root = $this->injector->getPath('views') . $this->params['controller'] . DIRECTORY_SEPARATOR;

        $this->template = new ActionViewBase();
        // $this->template = new ActionView:::Base();
        $this->template->__base= $this->config->getProperty('document_root');
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

    // XXX: not done.
    protected function redirect_to($action, $controller= NULL, $params = array()) {
        // get the curent controller, if NULL is passed.
        if (is_null($controller)) $controller= $this->params['controller'];

        if ($this->config->getProperty('rewrite')) {
            $this->response->redirect(
                $this->config->getProperty('server_name') . $this->config->getProperty('document_root') . '/' .
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
                throw new RouteException(
                    'Cannot invoke default action, \'index\' for this Route!',
                    'Method named \'index\' is not defined in class: ' . $this->getClassName()
                );
            }
        }

        $this->params['action'] = strtolower($action_name);
        $this->logger->debug('Incoming action:: ' . strtolower($action_name));
        // invoke the action.
        $action->invoke($this);
        if ($this->action_performed) return;
        // try to load the magick __common method.
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
     *          // 2) a filter must return void, in case of a failure,
     *          // use the redirect method.
     *          protected function authenticate() {
     *              // authentication code here
     *          }
     *      }
     * </code>
     * @access private
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
                    'Could not create filter: `'.$filter_name.'`, skipping...');
                continue;
            }
            // a filter should be declared as protected.
            if (!$filter->isProtected()) {
                throw new MedickException(
                    'Your filter,`'. $filter_name . '` is declared as a
                        public method of class `' . $this->getClassName() .'` !');
            }
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
                $this->logger->debug('Injecting Model:: ' . $model);
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
     * TODO: can we move this to the Object class?
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

// }
