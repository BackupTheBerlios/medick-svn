<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
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

include_once('action/controller/Map.php');
include_once('action/controller/Route.php');
include_once('action/controller/Routing.php');
include_once('action/controller/Request.php');
include_once('action/controller/Response.php');
include_once('action/controller/Base.php');
include_once('context/ContextManager.php');
include_once('logger/Logger.php');

/**
 * It knows how to dispatch a request
 * 
 * The role of this class will increase in the next versions.
 *
 * @package medick.action.controller
 * @author Oancea Aurelian
 */
class Dispatcher extends Object {

    private $manager;
    
    public function Dispatcher(ContextManager $manager) {
        $this->manager= $manager;
    }
    
    /**
     * Framework entry point
     * @return void.
     */
    public function dispatch() {
        $request  = new HTTPRequest();
        $response = new HTTPResponse();
        try {
            $configurator= $this->manager->getConfigurator();
            Registry::put($configurator, '__configurator');
            Registry::put($logger= new Logger($configurator), '__logger');
            $logger->debug('Config File: ' . $configurator->getConfigFile());
            $ap= $configurator->getApplicationPath();
            $an= $configurator->getApplicationName();
            $logger->debug("{app.name} -> $an");
            $routes_path= $ap . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . $an . '.routes.php';
            include_once($routes_path);
            $logger->debug("routes loaded from: $routes_path");
            $logger->debug("Medick {" . Medick::getVersion() . "} ready to dispatch.");
            ActionControllerRouting::recognize($request)->process($request, $response)->dump();
        } catch (Exception $ex) {
            ActionController::process_with_exception($request, $response, $ex)->dump();
            $logger->warn($ex->getMessage());
        }
    }
}

