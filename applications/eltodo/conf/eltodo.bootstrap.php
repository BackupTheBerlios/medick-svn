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
// $Id: application.bootstrap.php 164 2005-10-01 16:36:01Z aurelian $
// 
// ///////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * Sample __APPLICATION__NAME__.bootsrap.php file
 * Will bootstrap the application by setting it`s propreties.
 * Required files for start-up are included here
 * @package locknet7.start
 */

// error reporting level, turn this off in production!
error_reporting(E_ALL|E_STRICT);

if (version_compare(PHP_VERSION, '5.1.0') <= 0) {
    date_default_timezone_set('Europe/Bucharest');
}

// $pathinfo = pathinfo(__FILE__);
// $file     = explode('.',$pathinfo['basename']);

// application name
define('APP_NAME', 'eltodo');

// main TOP_LOCATION.
define('TOP_LOCATION', '/wwwroot/medick/trunk/');

// include_path, rewrite the existing one
set_include_path( TOP_LOCATION . 'libs'   . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR
                );

// load core classes.
include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/ErrorHandler.php');
include_once('medick/Registry.php');
include_once('medick/Dispatcher.php');
include_once('medick/Version.php');

// set-up the error handler:
restore_error_handler();
set_error_handler(array(new ErrorHandler(), 'raiseError'));

// hook a Configurator into Registry.
include_once('configurator/XMLConfigurator.php');
Registry::put(new XMLConfigurator('/wwwroot/medick/applications/eltodo/conf/eltodo.xml'), '__configurator');
// include_once('configurator/INIConfigurator.php');
// Registry::put(new INIConfigurator('/wwwroot/medick/applications/eltodo/conf/eltodo.ini'), '__configurator');

// core loaded.
include_once('logger/Logger.php');
$logger= new Logger();
$logger->debug('Core Loaded...');
$logger->debug('Running on Medick $v:' . Version::getVersion());
$logger->debug('Bootstrapped: ' . APP_NAME . '.bootstrap.php');
$logger->debug('XML Config File: ' . APP_NAME . '.xml');
$logger->debug('Routes File: ' . APP_NAME . '.routes.php');
Registry::put($logger, '__logger');

// get some orientation.
include_once('action/controller/Map.php');
$map= Registry::put(new Map(), '__map');

// load application map.
include_once('/wwwroot/medick/applications/eltodo/conf/eltodo.routes.php');

?>
