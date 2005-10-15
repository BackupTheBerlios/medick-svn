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

include_once('configurator/ConfiguratorException.php');

/**
 * Configurator Interface.
 * @package locknet7.config
 */
interface IConfigurator {
    
    /**
     * It gets the logger outputters.
     * @return Iterator
     */
    function getLoggerOutputters();
    
    /**
     * It get logger formatter
     * @return string, Logger formatter name eg. FooFormatter.
     */
    function getLoggerFormatter();
    
    /**
     * Propery parser
     * @param String the property name
     * @return String, the property value
     * @throws ConfiguratorException if the property is not found
     */
    function getProperty($name);
    
    /**
     * Based on id we return the dsn array
     * <code>
     *      // for Creole this dsn format will do the job:
     *      $dsn = array('phptype'=>'mysql','hostspec'=>'localhost','username'=>'root','password'=>'','database'=>'test'); 
     * </code>
     * @param string, id, [optional]the dsn id, if none is specified, we will use the default
     * @return array, dsn ready to use
     * @throws ConfiguratorException if the id is not found
     */
    function getDatabaseDsn($id = FALSE);
}
