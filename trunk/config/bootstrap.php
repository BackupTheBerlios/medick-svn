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

/**
 * Will bootstrap the application by setting it`s propreties.
 * Required files for start-up are included here
 * @package locknet7.start
 */

// error reporting level, turn this off in production!
error_reporting(E_ALL|E_STRICT);

// main TOP_LOCATION.
define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

// include_path, rewrite the existing one
set_include_path( TOP_LOCATION . 'libs'   . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR
                );

// XXX.
if (phpversion()=='6.0.0-dev') {
     // strict sdandards.
     date_default_timezone_set("Europe/Bucharest");
}

// load core classes.    
include_once('MedickException.php');
include_once('configurator/XMLConfigurator.php');
// setup a Configurator.
Configurator::factory('XML');
// get some orientation.
include_once('action/controller/Map.php');
// load application map.
include_once(TOP_LOCATION . 'config' . DIRECTORY_SEPARATOR . 'routes.php');

include_once('logger/Logger.php');
include_once('Dispatcher.php');
