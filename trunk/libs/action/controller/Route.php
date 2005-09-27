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

// include_once('action/controller/route/MedickRoute.php');
include_once('action/controller/route/RouteException.php');

/**
 * @package locknet7.action.controller.route
 */
class ActionControllerRoute {

    public static function recognize(Request $request) {
        // XXX. if failed?
        $controller= $request->getParam('controller');
        // XXX. if failed?
        $action    = $request->getParam('action');
        $map= Map::getInstance();
        if ($route= $map->contains(new Route($controller,$action))) {
            foreach ($route->getParams() AS $param) {
                if (!$request->hasParam($param->getName()) OR ($request->getParam($param->getName()) =='')) {
                    // XXX. load failure due to missing parameters.
                    $route= $map->getRouteByName($route->getFailure());
                    // XXX. failure message.
                    $request->setParam('controller', $route->getController());
                    $request->setParam('action', $route->getAction());
                    break;
                    // throw new RouteException('Route failed due to the missing parameters!');

                }
                if ($param->hasValidators()) {
                    foreach($param->getValidators() AS $validator) {
                        $validator->setValue($request->getParam($param->getName()));
                        if (!$validator->validate()) {
                          // XXX. validation failed.
                          throw new RouteException('Route validation failed!');
                        }
                    }
                }
            }
            return self::createController($route);
        }
        // XXX. no exception here, default rout must be loaded.
        throw new RouteException('Our map do not contain this Route!');
    }

    /**
     * Creates an instance of the requested Controller
     * In case the route fails, we try to load the default controller specified in the config file
     * @param Request, request, the request
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
