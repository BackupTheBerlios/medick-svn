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

include_once('action/controller/Route.php');
include_once('action/controller/route/RouteParam.php');

/**
 * @package locknet7.action.controller.map
 */

class Map {

    /** @var Map, the current Map */
    private static $instance = NULL;

    /**
     * It gets our map instance
     */
    public static function getInstance() {
        return self::$instance;
    }

    /** @var array, routes */
    private $routes;

    /** Create a new Map Object */
    public function __construct() {
        $this->routes= array();
        self::$instance= $this;
    }

    /**
     * Adds a route to this map
     * @param Route route
     */
    public function add(Route $route) {
        if ($this->contains($route)) return;
        $this->routes[]=$route;
    }

    /**
     * It gets a route by his name
     * @param string the name of the route to look for.
     * @throw MedickRouteException
     */
    public function getRouteByName($name) {
        foreach ($this->routes AS $route) {
            if ($route->getName()==$name) {
                return $route;
            }
        }
        throw new RouteException('Cannot find a route with this name: ' . $name . ' !');
    }

    /**
     * Check if the current Map contains the given route
     * If so, we return-it, otherwise we return false
     * @param Route route
     * @return Route or FALSE if this map don`t contain this route.
     */
    public function contains(Route $route) {
        foreach ($this->routes AS $_route) {
            if ($_route->getId()==$route->getId()) {
                return $_route;
            }
        }
        return FALSE;
    }
}