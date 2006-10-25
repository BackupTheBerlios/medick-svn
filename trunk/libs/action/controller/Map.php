<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian[at]locknet[dot]ro>
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

/**
 * A Map is a Collection of Routes
 * 
 * @package medick.action.controller
 * @subpackage routing
 * @author Oancea Aurelian
 */
class Map extends Collection {

    private static $instance= NULL;

    /**
     * Hidden constructor
     *
     * Use Map::getInstance to get an instance
     */ 
    protected function Map() {
        parent::Collection();
    }

    /**
     * It gets this object instance
     * 
     * @return Map
     */ 
    public static function getInstance() {
        if (Map::$instance===NULL) {
            Map::$instance= new Map();
        }
        return Map::$instance;
    }
    
    /**
     * Match the incoming Request against defined Routes
     *
     * @param Request incoming request
     * @throws RoutingException if we cannot resolve this request to a route
     */ 
    public function match(Request $request) {
        foreach ($this->elements as $element) {
            if ($element->match($request)) return $element;
        }
        throw new RoutingException('Cannot find a Route for this hash: ' . $request->getRequestURI());
    }
    
    /**
     * It gets a Route of this Map by the Route name
     *
     * @param string route name
     * @throws RoutingException if we cannot find a Route by the givven name
     */ 
    public function getRouteByName($name) {
        foreach ($this->elements as $element) {
            if ($element->getName() == $name) return $element;
        }
        throw new RoutingException('Cannot find a Route with this name: ' . $name); 
    }
}

