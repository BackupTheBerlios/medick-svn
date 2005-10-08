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
 * @package locknet7.action.controller.route
 */
class Route extends Object {

    /** @var string
        this route name */
    private $name = '';
    
    /** @var string
        route controller name */
    private $controller;
    
    /** @var string
        route action name */
    private $action;
    
    /** @var string 
        unique route identifier. */
    private $id;
    
    /** @var array RouteParam[]
        route parameters. */
    private $params;
    
    /** @var mixed
        route access level.*/
    private $access;
    
    /** @var array
        distinctive route headers*/
    private $headers;
    
    /** @var string 
        name of the failure Route.*/
    private $failure;

    /**
     * Constructor.
     * @param string controller name
     * @param string action name
     */
    public function __construct($controller, $action= '') {
        $this->controller= $controller;
        $this->action= $action;
        $this->id= md5($this->controller . $this->action);
        $this->params= array();
    }
    
    /**
     * Set the failure Route name
     * @param string name, the name of the failure route.
     */
    public function setFailure($name) {
        $this->failure = $name;
    }

    /**
     * Gets the failure Route name
     * @return string
     */
    public function getFailure() {
        return $this->failure;
    }

    /**
     * It gets this route id,
     * a md5`ed concat`ed string between controller+action
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the name of this route.
     * @param string name
     */
    public function setName($name) {
        $this->name= $name;
    }

    /**
     * Gets the name of this route.
     * @return string name of this route.
     */
    public function getName() {
        return $this->name == '' ? $this->controller . '/' . $this->action : $this->name;
    }

    /**
     * It sets the action for this route.
     * @param string action action name
     */
    public function setAction($action) {
        $this->action= $action;
    }

    /**
     * It gets the action.
     * @return string action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * It get the controller
     * @return string controller, like 'todo'
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * The controller name like 'TodoController'
     * @return string controller name
     */
    public function getControllerName() {
      return ucfirst($this->controller) . 'Controller';
    }

    /**
     * It gets the controller path
     * like /home/user/app/controllers/
     * @deprecated, this method should be merged with getControllerFile.
     * @return string the controller path
     */
    public function getControllerPath() {
        return
            Registry::get('__configurator')->getProperty('application_path') .
            DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
    }

    /**
     * It gets the controller file like 'todo_controller.php'
     * @deprecated, this method should be merged with getControllerPath()
     * @return string the controller file
     */
    public function getControllerFile() {
        return strtolower($this->controller) . '_controller.php';
    }

    /**
     * It sets the access level.
     * @param AccessLevel access
     */
    public function setAccess($access) {
        $this->access= $access;
    }

    /**
     * Adds a new Parameter on this route or a Header.
     * @param Object argument
     */
    public function add($argument) {
        if ($argument instanceof RouteParam) {
            $this->params[]  = $argument;
        } elseif ($argument instanceof RouteHeader) {
            $this->headers[] = $argument;
        } else {
            throw new RouteException('Wrong Argument Type in ' . __METHOD__ . ', Argument should be one of RouteParam or RouteHeader objects!');
        }
        return $argument;
    }
    
    /**
     * Add Routing Components from an array
     * @param array params to add
     */
    public function addFromArray($params) {
        foreach($params as $param) {
            $this->add($param);   
        }
    }

    /**
     * Gets the list with the attached params
     * @return array RouteParam[]
     */
    public function getParams() {
        return $this->params;
    }

    /** 
     * It gets the list of headers
     * @return array RouteHeaders
     */
    public function getHeaders() {
        return $this->headers;
    }
}
