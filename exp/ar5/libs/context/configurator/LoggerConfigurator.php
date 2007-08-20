<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
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
// $Id: LoggerConfigurator.php 452 2007-08-15 08:06:49Z aurelian $
//
// ///////////////////////////////////////////////////////////////////////////////
// }}}

include_once('context/configurator/IConfigurator.php');

/**
 * A plain Application Configurator
 * 
 * @package medick.configurator
 * @author Aurelian Oancea
 */ 
class LoggerConfigurator extends Object implements IConfigurator {
    /** @see medick.configurator.IConfigurator::getLoggerOutputters() */
    public function getLoggerOutputters() {
        return array(array('name' => 'stdout','level' => '0'));
    }
    /** @see medick.configurator.IConfigurator::getLoggerFormatter */
    public function getLoggerFormatter() {
        return 'SimpleFormatter';
    }
    /** @see medick.configurator.IConfigurator::getProperty(string name) */
    public function getProperty($name) {     }
    /** @see medick.configurator.IConfigurator::getDatabaseDsn(bool id) */
    public function getDatabaseDsn($id = FALSE) {  }
    
}
