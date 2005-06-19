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
 * ActionControllerMap is a better name?
 * Create an instance of requested controller
 */
class ActionControllerRoute {

    //
    public function add(Route $route) {       }
    
    /**
     * Creates an instance of the requested Controller
     * TODO: In case of a failure we must try to load the default controller (see [1])
     * @param Request, request, the request
     * @return ActionController
     */
    public static function createController(Request $request) {
        $logger     = Logger::getInstance();
        $route      = new MedickRoute($request->getParam('controller'));

        //xxx[1]
        if (!self::exists($route)) throw new Exception ('Route Failure!');
        
        $logger->debug('Incoming controller:: ' . $route->getControllerName());
        
        $request->setRoute($route);
        
        include_once('application.php');
        include_once($route->getControllerFile());
        
        $controller_class = new ReflectionClass($route->getControllerName());
        // start inspection.
        if ( ($controller_class->isInstantiable()) AND ($controller_class->getParentClass()->name=='ApplicationController') ) {
            return $controller_class->newInstance();
        } 
    }

    /**
     * A nice method to check if the given Route Exists.
     * <code>
     *      ActionControllerRoute::exists(new MedickRoute($request->getController());
     * </code>
     * @access public
     * @param Route, route, the route
     * @return bool, true if the controller exists false otherwise.
     */
    public static function exists(Route $route) {
        if ($route->getControllerName() === NULL) return FALSE;
        if (!is_file($route->getControllerPath() . $route->getControllerFile())) return FALSE;
        return TRUE;
    }

}

interface Route {
    function getControllerName();
    function getControllerPath();
    function getControllerFile();
}

class MedickRoute implements Route {

    /** We recieve from request the controller in this form <tt>news</tt>
     *  the internal object name for this controller will be <tt>NewsController</tt> */
    private $ctrl_name;
    
    /** By default, 
     * all the controllers resids in top_location/app/controllers/*/
    private $ctrl_path;
    
    /** By default, 
     *  the controller file will be located on $controller_path/$request->getParam('controller')_controller.php*/
    private $ctrl_file;
    
    /**
     * Constructor...
     * It builds this ROUTE
     * @param string, controller_name, controller name
     */
    public function __construct($controller_name) {
        $this->ctrl_name = is_null($controller_name) ? NULL : ucfirst($controller_name) . 'Controller';
        $this->ctrl_path = TOP_LOCATION . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $this->ctrl_file = strtolower($controller_name) . '_controller.php';
    }

    /** It gets the Controller Name */
    public function getControllerName() {
        return $this->ctrl_name;
    }

    /** It gets the controller path */
    public function getControllerPath() {
        return $this->ctrl_path;
    }

    /** It gets the controller file */
    public function getControllerFile() {
        return $this->ctrl_file;
    }
    /** a Representation of this object as a string */
    public function toString() {
        return $this->ctrl_name;
    }
    /** php magic, in 5.0.4 is toString() not __toString(). */
    // public function __toString() {
    //    return $this->toString();
    //}
}
