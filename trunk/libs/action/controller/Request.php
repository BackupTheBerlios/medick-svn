<?php

/** @package locknet7.Action.Controller.Request
 * $Id $
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
