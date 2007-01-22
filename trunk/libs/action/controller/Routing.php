<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2006 Oancea Aurelian < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Oancea Aurelian nor the names of his contributors may
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
 * It knows how to create a Controller from the URL
 * 
 * @package medick.action.controller
 * @subpackage routing
 * @author Oancea Aurelian
 */
class ActionControllerRouting extends Object {

    /**
     * Finds the Route on this Map using the Request
     * 
     * @param request Request
     * @return Route
     * @throws RoutingException
     */ 
    private function findRoute(Request $request) {
        return Map::getInstance()->match($request);
    }

    /**
     * Recognize a Route Based on the Request.
     *
     * @param Request request the Request
     * @return ActionController
     * @throws RoutingEception
     */
    public static function recognize(Request $request) {
        $r= new ActionControllerRouting($request);
        try {
            $route = $r->findRoute($request);
            return   $route->createControllerInstance($request);
        } catch (RoutingException $rEx) {
            // exception thrown by findRoute if we dont match any of the registered route.
            // load 404 route, if fails too try the default route, this are named routes.
            // echo $rEx;
            try {
                return Map::getInstance()->getRouteByName(Route::NOTFOUND)->createControllerInstance($request);
            } catch (RoutingException $rEx2) {
                return Map::getInstance()->getRouteByName(Route::WELCOME)->createControllerInstance($request);
            }
        }
    }
}
