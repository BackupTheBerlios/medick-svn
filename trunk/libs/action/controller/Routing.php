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

include_once('action/controller/route/RouteException.php');

/**
 * @package locknet7.action.controller.route
 */
class ActionControllerRouting extends Object {

    /**
     * Check if the application Map contains the current Route.
     */
    public static function recognize(Request $request) {
        $map= Registry::get('__map');
        // do we know this Route?
        if ($route= $map->contains(new Route($request->getParam('controller'), $request->getParam('action')))) {
            $is_failure= FALSE;
            $params= $route->getParams();
            // {{{ loop throught the Route Parameters
            foreach ($params AS $param) {
                
                // {{{ if this Request has the current parameter, try to validate him.
                if (!$request->hasParam($param->getName()) OR ($request->getParam($param->getName()) =='')) {
                    // XXX. load failure due to missing parameters.
                    $is_failure= TRUE;
                    
                    // XXX. failure message.
                    // break;
                } // }}}
                // {{{ if this paramester has attached validators,
                if ($param->hasValidators()) {
                    // loop throught the validators and validate this parameter value.
                    foreach($param->getValidators() AS $validator) {
                        $validator->setValue($request->getParam($param->getName()));
                        if (!$validator->validate()) {
                          // XXX. validation failed, handle automatically and break(?)
                          throw new RouteException('Route validation failed!');
                        }
                    }
                } // }}}
                $param->setValue($request->getParam($param->getName()));
            } // end foreach. }}}
            if ($is_failure) {
                $route= $map->getRouteByName($route->getFailure());
                $route->addFromArray($params);
            }
        } else {
            $route= $map->getRouteByName('default');
        }

        // overwrite incoming core parameters.
        $request->setParam('controller', $route->getController());
        $request->setParam('action', $route->getAction());
        $map->setCurrentRoute($route);
        return self::createController($route);
    }

    /**
     * Creates an instance of the requested Controller
     * 
     * @param Route route to create the controller for.
     * @return ActionController
     * @throws RouteException
     */
    private static function createController(Route $route) {
        // this will fail in ReflectionClass.
        @include_once($route->getControllerPath() . 'application.php');
        @include_once($route->getControllerPath() . $route->getControllerFile());
        // XXX. try/catch here.
        $controller_class = new ReflectionClass($route->getControllerName());

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
}

