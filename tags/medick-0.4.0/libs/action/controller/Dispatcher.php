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
 * It reads the configuration file and loads the application routes
 *
 * @package medick.action.controller
 * @author Aurelian Oancea
 */
class Dispatcher extends Object {

    private $manager;
    
    public function Dispatcher(ContextManager $manager) {
        $this->manager= $manager;
    }
    
    /**
     * Framework entry point
     *
     * @return void.
     */
    public function dispatch() {
        $request  = new HTTPRequest();
        $response = new HTTPResponse();
        try {
            $configurator= $this->manager->getConfigurator();
            Registry::put($configurator, '__configurator');
            Registry::put($logger= new Logger($configurator), '__logger');
            $ap= $configurator->getApplicationPath(); // application path
            $an= $configurator->getApplicationName(); // application name
            $logger->debug('[Medick] >> version: ' . Medick::getVersion() . ' ready for ' . $an);
            $logger->debug('[Medick] >> Application path ' . $ap);
            $routes_path= $ap . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . $an . '.routes.php';
            include_once($routes_path); // load routes
            $logger->debug('[Medick] >> Config File: ' . str_replace($ap, '${'.$an.'}', $configurator->getConfigFile()) );
            $logger->debug('[Medick] >> Routes loaded from: '. str_replace($ap, '${'.$an.'}', $routes_path));
            ActionControllerRouting::recognize($request)->process($request, $response)->dump();
        } catch (Exception $ex) {
            ActionController::process_with_exception($request, $response, $ex)->dump();
            $logger->warn($ex->getMessage());
        }
    }
}
