<?php
// {{{ HEADER
/********************************************************************************************************************
 * $Id$
 *
 * Copyright (c) 2005, Oancea Aurelian
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 *******************************************************************************************************************/
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

    /** XXX:
    public static function controllerName() {
        return __CONTROLLER__NAME__;
    }

    public static function controllerPath() {
        return __CONTROLLER__PATH__;
    }
    */

    /**
     * Will process the request returning the resulting response
     * @param Request request, the request
     * @param Response response, the response
     * @return Response
     */
    public function process(Request $request, Response $response) {
        // start template.
        $this->assign_shortcuts($request, $response);
        $this->logger->debug('Shortcuts assigned...');

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
        // $this->session = $request->getSession();
        // $this->params  = $request->params;
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
        if( is_null($action_name) ) $action_name = 'index';
        $this->logger->debug('Incoming action:: ' . strtolower($action_name));
        if ($action_name == 'process') throw new Exception('Cannot call internal process method');
        $action = $this->callMethod($action_name);
        if ($action->isConstructor()) throw new Exception('Calling constructor is not allowed');
        if ($action->isDestructor())  throw new Exception('Cannot call destructor');
        if ($action->isStatic())      throw new Exception('Cannot invoke a static method!');
        return $action->invoke($this);
    }

    private function add_before_filters() {
        if (isset($this->before_filter)) {
            foreach($this->before_filter AS $filter_name) {
                $filter = $this->callMethod($filter_name);
                if (!$filter->isProtected()) throw new Exception('Your filter is declared as public!');
                $this->$filter_name();
                // $filter->invoke($this); will work only for public methods.
            }
        }
    }

    private function add_models() {
        if (isset($this->model)) {

            if ( count($this->model) > 1 ) {
                $this->logger->warn('Only One Model allowed, running on the first one...');
            }

            $this->logger->debug("We have Models...");
            // foreach ($this->model AS $model) {
            $this->logger->debug('Injecting Model:: ' . $this->model[0]);
            ModelInjector::inject($this->model[0]);
            // }
        }
    }

    private function callMethod($method_name) {
        return new ReflectionMethod($this, strtolower($method_name));
    }

}
