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

include_once('action/controller/Injector.php');

/**
 * @package locknet7.action.controller.route
 */
class ActionControllerRouting extends Object {

    /**
     * Check if the application Map contains the current Route.
     *
     * If so, we add the route parameters into the current request.
     *
     * @param locknet7.action.controller.Request
     * @return locknet7.action.controller.Base
     */
    public static function recognize(Request $request) {
        $map   = Registry::get('__map');
        $logger= Registry::get('__logger');
        // do we know this route?
        if ($route= $map->contains(
                                new Route(
                                    $request->getParam('controller'), 
                                    $request->getParam('action')))
                                  )
        {
            $logger->debug('Route Recognized: ' . $route->getName());
            $params= $route->getParams();
            foreach ($params as $key=>$param) {
                $request->setParam($param->getName(), $request->getPathInfo($key));
            }
        } else {
            $logger->debug('Unknown Route! {' . 
                            $request->getParam('controller') . 
                            '/'. $request->getParam('action') . 
                            '} Loading default...');
            $route= $map->getRouteByName('default');
            $request->setParam('controller', $route->getController());
            $request->setParam('action', $route->getAction());
        }
        $logger->debug(
            'Running on Route: ' . $route->getName() .
            ' ctrller: ' . $route->getController() .
            ' act: ' . $route->getAction());
        $map->setCurrentRoute($route);
        return Registry::put(new Injector(), '__injector')->inject('controller', $route->getController());
    }
}
