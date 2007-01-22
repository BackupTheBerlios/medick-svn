<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Oancea Aurelian < aurelian [ at ] locknet [ dot ] ro >
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
 * Configurator Interface is the central point for providing 
 * configurations for a medick applications
 *
 * While the application is running, the configurations options are read-only,
 * but one might provide methods for writing or changing values.
 *
 * A configuration is resolved using the application name. 
 * Usually, the place for keeping configuration file is under conf/application_name.(xml|ini)
 *
 * The olny implementation that it's working right now is XMLConfigurator.
 * Also, a plain php code configurator is used for testing the Logger.
 *
 * From medick 0.2, the options will be splitted based on context, and we will
 * provide web specific configuration section as well as logger and database contextes.
 * 
 * From medick 0.3.0 this class and the old configuration methods will be removed
 * 
 * @package medick.configurator
 * @see XMLConfigurator
 * @see LoggerConfigurator
 * @author Oancea Aurelian
 */
interface IConfigurator {

    /**
     * It gets the logger outputters.
     *
     * @return array
     */
    function getLoggerOutputters();

    /**
     * It gets the logger formatter
     *
     * @return string, Logger formatter name eg. FooFormatter.
     */
    function getLoggerFormatter();

}
