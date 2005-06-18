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
 * @package locknet7.action.controller.base
 */
    
include_once('action/controller/Dependency.php');
 

/**
 * Base Class For Our Application Controllers
 */
class ActionControllerBase {
    /** logger instance */
    protected $logger;
    /** Request */
    protected $request;
    /** Request parameters */
    protected $params;
    /** Response */
    protected $response;
    /** Request Heders */
    protected $headers;
    /** Session */
    protected $session;
    /** values for template class */
    protected $assigns;

    /**
     * Will process the request returning the resulting response
     * @param Request request, the request
     * @param Response response, the response
     * @return Response
     */
    public function process(Request $request, Response $response) {
        // todo. start template.
        $this->assign_shortcuts($request, $response);

        $this->add_before_filters();
        $this->add_models();

        $this->perform_action($request->getParam('action'));
        return $response;
    }

    /**
     * Act as an internal constructor.
     * @param Request request, the request
     * @param Response response, the response
     * @return void
     */
    private function assign_shortcuts(Request $request, Response $response) {
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = Logger::getInstance();
        $this->session  = $request->getSession();
        $this->params   = $request->getParams();
    }

    // do we need smthing like this???
    public function actionName() {
        return isset($this->params['action']) ? $this->params['action'] : 'index';
    }

    // XXX: not-done!
    protected function sendFile($location, $options = array()) {
        if(!is_file()) {
            throw new Exception("File not found...");
        }
        // $options['length'] = File->size($location);
        // $options['filename'] = File->basename($location);
    }

    // XXX: not-done
    protected function sendData($data, $options = array()) {

    }

    // XXX: not done.
    protected function redirect() {

    }

    /**
     * Performs the action
     * @param string the action name
     * @return result, the result of the action invocation.
     */
    private function perform_action($action_name) {
        $forbidden_actions = array('process', '__construct', '__destruct');
        if( 
            (is_null($action_name)) OR 
            (in_array($action_name, $forbidden_actions))
          )
        {
            $action_name = 'index';
        }
        $this->logger->debug('Incoming action:: ' . strtolower($action_name));
        $action = $this->createMethod(strtolower($action_name));
        if ($action->isStatic()) throw new Exception('Cannot invoke a static method!');
        return $action->invoke($this);
    }
    
    /**
     * It executes before filters
     *
     * Loop on before_filter array, invoking the method before the action
     * <code>
     *      class news_controller extends ActionControllerBase {
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
                if (!$filter->isProtected()) throw new Exception('Your filter is declared as public!');
                $this->$filter_name();
                // $filter->invoke($this); will work only for public methods.
            }
        }
    }

    private function add_models() {
        if (isset($this->model)) {

            if ( count($this->model) > 1 ) {
                $this->logger->warn('At this point, only One Model is allowed, running on the first one...');
            }

            $this->logger->debug("We have Models...");
            // foreach ($this->model AS $model) {
            $this->logger->debug('Injecting Model:: ' . $this->model[0]);
            ModelInjector::inject($this->model[0]);
            // }
        }
    }
    
    private function createMethod($method_name) {
        return new ReflectionMethod($this, strtolower($method_name));
    }
    

}
