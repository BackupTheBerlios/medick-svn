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


class HTTPRequest implements Request {

    /** HTTP Parameters 
        via GET __and__ POST */
    protected $params;
    
    /** HTTP method */
    protected $method;
    
    /** Session */
    protected $session;
    
    /** The Route */
    protected $route;

    /**
     * Constructor.
     * It builds the HTTPRequest object
     */
    public function __construct() {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ? 'POST' : 'GET';
        $this->params  = $_REQUEST; // to test!
        unset($_REQUEST); // hack or feature? :)
    }
    
    /**
     * It gets all the parameters of this Request
     * @return array params
     */
    public function getParams() {
        return $this->params;
    }
    
    /**
     * It gets the Session
     * @return Session, the curent Session
     */
    public function getSession() {
        return $this->session;
    }
    
    /**
     * It gets the param
     * @param mixed, param, the paremeter name
     * @return the param value of NULL if this param was not passed with this Resuest
     */
    public function getParam($param) {
        return isset($this->params[$param]) ? $this->params[$param] : NULL;
    }

    /**
     * It gets the Route used for this Request
     * @return Route, the route 
     */
    public function getRoute() {
        return $this->route;
    }
    
    /**
     * It sets the Request Route
     * @param Route route, the route to set on this Request
     * @return void
     */
    public function setRoute(Route $route) {
        $this->route = $route;
    }
    
    /**
     * It get the method name used for this Request (GET or POST)
     * @return string method name (GET/POST)
     */
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * Check if the current method is get
     * TODO: do I need smthing like this?
     * @return bool TRUE if the method is GET, false otherwise
     * @deprecated: I will remove this method next week
     */
    public function isGet() {
        return $this->method == 'GET';
    }
    
    /**
     * Check if the current method is post
     * TODO: do I need smthing like this?
     * @return bool TRUE if the method is POST, false otherwise
     * @deprecated: I will remove this method next week
     */
    public function isPost() {
        return $this->method == 'POST';
    }
    
    // {{{ todos.
    
    // XXX
    public function getIP() {  }
    
    // XXX
    public function getRequestURI() {  }
    
    // XXX: 
    public function getProtocol() {  }
    
    // }}}
    
}
