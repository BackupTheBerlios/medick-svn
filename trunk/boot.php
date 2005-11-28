<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
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
//////////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * It boots a medick application
 * @package locknet7.boot
 */

define( 'MEDICK_PATH', dirname(__FILE__)  . DIRECTORY_SEPARATOR );
// medick path.
set_include_path( MEDICK_PATH . 'libs'   . DIRECTORY_SEPARATOR  );
// this should depend on environment
error_reporting(E_ALL|E_STRICT);
// php 5.1 strict sdandards.
if (version_compare(PHP_VERSION, '5.1.0') > 0) {
    date_default_timezone_set('Europe/Bucharest');
}
// load core classes
include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/ErrorHandler.php');
set_error_handler(array(new ErrorHandler(), 'raiseError'));
include_once('medick/Registry.php');
include_once('medick/Dispatcher.php');
include_once('medick/Version.php');
include_once('configurator/XMLConfigurator.php');
include_once('logger/Logger.php');
include_once('action/controller/Map.php');

$conf_files = $_SERVER['MEDICK_APPLICATION_PATH'] . DIRECTORY_SEPARATOR . 'conf' . 
                        DIRECTORY_SEPARATOR . $_SERVER['MEDICK_APPLICATION_NAME'];
                        
Registry::put(new XMLConfigurator($conf_files . '.xml'), '__configurator');

$map= Registry::put(new Map(), '__map');

$logger= Registry::put(new Logger(), '__logger');

$logger->debug('Core Loaded...');
$logger->debug('Running on Medick $v: ' . Version::getVersion());
$logger->debug('XML Config File: ' . $conf_files . '.xml');
$logger->debug('Routes File: ' . $conf_files . '.routes.php');

include_once($conf_files . '.routes.php');
