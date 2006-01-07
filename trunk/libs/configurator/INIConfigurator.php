<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005,2006 Oancea Aurelian <aurelian@locknet.ro>
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

include_once('configurator/IConfigurator.php');

/**
 * ini file-based Configurator.
 * @package locknet7.config
 */

class INIConfigurator extends Object implements IConfigurator {

    private $handler;

    public function __construct($file) {
      $this->handler= parse_ini_file($file, TRUE);
    }

    public function getLoggerOutputters() {
        $ret= array();
        $ret[0]=$this->handler['logger.file.outputter'];
        $ret[0]['name'] = 'file';
        $ret[1]=$this->handler['logger.mail.outputter'];
        $ret[1]['name']= 'mail';
        $ret[2]=$this->handler['logger.stdout.outputter'];
        $ret[2]['name']= 'stdout';
        $ao = new ArrayObject($ret);
        return $ao->getIterator();
    }

    public function getLoggerFormatter() {
        return ucfirst($this->handler['logger']['formatter']) . 'Formatter';
    }

    public function getProperty($name) {
        if (isset($this->handler['properties'][$name])) {
            return $this->handler['properties'][$name];
        } else {
            throw new Exception('Property: ' . $name . ' not found!');
        }
    }

    public function getDatabaseDsn($id = FALSE) {
        if (!$id) $id= $this->handler['database']['default'];
        return $this->handler['database.' . $id];
    }

}

