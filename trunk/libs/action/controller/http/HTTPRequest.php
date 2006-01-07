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

include_once('action/controller/session/Session.php');

/**
 * @package locknet7.action.controller.request
 */
class HTTPRequest extends Request {

    /** @var Session */
    private $session;

    /** @var string
        path_info_parts */
    private $path_info= NULL;

    /**
     * Constructor.
     * It builds the HTTPRequest object
     */
    public function __construct() {
        foreach ($_REQUEST as $key=>$value) {
            $this->setParameter($key, $value);
        }

        unset($_REQUEST); unset($_GET); unset($_POST);

        if (array_key_exists('PATH_INFO', $_SERVER)) {
            $this->path_info= $_SERVER['PATH_INFO'];
        }
        $this->session = new Session();
    }

    /**
     * It gets a part of the path info associated with this request
     * @param int, key, the part index
     * @return value of this part or NULL if this part is not defined
     */
    public function getPathInfo() {
        return $this->path_info;
    }

    public function getPathInfoParts() {
        if (is_null($this->path_info)) return array();
        return explode('/', trim($this->path_info,'/'));
    }

    /**
     * It gets the Session
     * @return Session, the curent Session
     */
    public function getSession() {
        return $this->session;
    }

    // {{{ todos.
    public function getIP() {  }
    public function getRequestURI() {  }
    public function getProtocol() {  }
    // }}}

}

