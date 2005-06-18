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
