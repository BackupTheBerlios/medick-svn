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
 * @package locknet7.action.controller.request
 */

interface Request {     }

abstract class AbstractHTTPRequest implements Request {

    /** HTTP Parameters via GET and POST */
    protected $params;
    /** HTTP method */
    protected $method;
    /** Session */
    protected $session;
    /** A logger instance */
    protected $logger;
    /** The Route */
    protected $route;
    // XXX: not-done
    // -> array_merge _POST, _GET
    // -> ___MAGIC
    // -> unset get/post
    public function getParams() {
        return $this->params;
    }

    public function getSession() {
        return $this->session;
    }
    
    public function getParam($value) {
        return isset($this->params[$value]) ? $this->params[$value] : NULL;
    }

    public function getRoute() {
        return $this->route;
    }

    public function setRoute(Route $route) {
        $this->route = $route;
    }
    
    /*
    public function getControllerPath() {
        return TOP_LOCATION . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
    }
    
    public function getControllerName() {
        return ucfirst($this->params['controller']) . 'Controller';
    }
    */
    //
    public function getMethod() {
        return $this->method;
    }
    //
    public function isGet() {
        return $this->method == 'GET';
    }
    //
    public function isPost() {
        return $this->method == 'POST';
    }
    // XXX
    public function getIP() {

    }
    // XXX
    public function getRequestURI() {

    }
    // XXX: see simple-test browser.
    public function getProtocol() {

    }
}


class HTTPRequest extends AbstractHTTPRequest {

    public function __construct() {
        $this->logger = Logger::getInstance();
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ? 'POST' : 'GET';
        $this->params  = $_REQUEST;
        unset($_REQUEST);
    }

}
