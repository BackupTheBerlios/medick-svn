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

include_once('action/controller/route/MedickRoute.php');
include_once('action/controller/route/RouteException.php');

/**
 * @package locknet7.action.controller.route
 */
class ActionControllerRoute {
    
    /**
     * Creates an instance of the requested Controller
     * In case the route fails, we try to load the default controller specified in the config file 
     * @param Request, request, the request
     * @return ActionController
     * @throws RouteException
     * TODO: refactor.
     */
    public static function createController(Request $request) {
        $controller = $request->getParam('controller');
        $passed     = 0;
        do {
            if ($passed == 1) {
                $controller = Configurator::getInstance()->getDefaultRoute()->controller;
                $request->setParam('controller', $controller);
            } elseif ($passed == 2) {
                throw new RouteException('Cannot create a Controller...');
            }
            $route = new MedickRoute($controller);
            $passed++;
        } while (!self::exists($route));

        $request->setRoute($route);
        
        include_once($route->getControllerPath() . 'application.php');
        include_once($route->getControllerPath() . $route->getControllerFile());
        
        $controller_class = new ReflectionClass($route->getControllerName());
        // start inspection.
        if ( 
            ($controller_class->isInstantiable()) 
            AND 
            (
                ($controller_class->getParentClass()->name=='ApplicationController')
                OR
                ($controller_class->getParentClass()->name=='ActionControllerBase')
            ) 
            )
        {
            return $controller_class->newInstance();
        }
        throw new RouteException ('Cannot create Controller...');
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
    public static function exists(IRoute $route) {
        if ($route->getControllerName() === NULL)
            return FALSE;
        if (!is_file($route->getControllerPath() . $route->getControllerFile())) 
            return FALSE;
        return TRUE;
    }
}
