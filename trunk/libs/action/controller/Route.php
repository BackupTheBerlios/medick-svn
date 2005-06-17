<?php
// {{{ HEADER
/********************************************************************************************************************
 * $Id: $
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
 * @package locknet7.action.controller.route
 * $Id $
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
        $request->setRoute($route);

        //xxx[1]
        if (!self::exists($route)) throw new Exception ('Route Failure!');
        
        $logger->debug('Incoming Controller:: ' . $route);
        
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
     *      ActionControllerRoute::exists(new MedickRoute($request->getController(), $request->getAction());
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
        return "Controller Name::<br />" . $this->ctrl_name . "<br />" . 
               "Controller Path::<br />" . $this->ctrl_path . "<br />" .
               "Controller File::<br />" . $this->ctrl_file . "<br />";
    }
    /** php magic, in 5.0.4 is toString() not __toString(). */
    public function __toString() {
        return $this->toString();
    }
}
