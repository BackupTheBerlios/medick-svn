<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
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
//   * Neither the name of Oancea Aureliano nor the names of his contributors may
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
 * A wrapper around PHP session handling
 *
 * @package medick.action.controller
 * @subpackage session
 * @author Oancea Aurelian
 */
class Session extends Object {

    /** @var bool
        started flag */
    private $isStarted = FALSE;

    /** @var ISessionContainer
        the session container */
    private $container;

    /**
     * Constructor, creates a new session object
     *
     * @throws IllegalStateException if the session is started
     */
    public function Session () {
        if ($this->isStarted) {
            throw new IllegalStateException('Session already Started!');
        }
    }

    /**
     * Starts a new Session
     *
     * Also, it setup our session preferences
     * @return void
     * @throws IllegalStateException if the session is already started
     */
    public function start() {
        if ($this->isStarted) {
            throw new IllegalStateException('Session already Started!');
        }
        // TODO: more settings
        // session_cache_limiter("nocache");
        // session_write_close();
        session_start();
        session_regenerate_id(TRUE);
        $this->isStarted= TRUE;
    }

    /**
     * Sets a session variable
     *
     * @param string name, the name of the session variable
     * @param mixed value, the value of the variable to set
     * @return void
     */
    public function putValue($name, $value) {
        $this->checkState();
        $_SESSION[$name] = $value;
    }

    /**
     * Gets a session variable value
     *
     * @param string name, the name of the session variable
     * @return NULL if the variable is not set, or mixed, the variable value
     */
    public function getValue($name) {
        return $this->hasValue($name) ? $_SESSION[$name] : NULL;
    }

    /**
     * Check if this session has a variable with the given name
     *
     * @param string name, the name of the session variable
     * @return bool, TRUE if it has
     */
    public function hasValue($name) {
        $this->checkState();
        return isset($_SESSION[$name]);
    }

    /**
     * Remove the session value with the given name
     *
     * @param string name, the name of the session variable
     * @return void
     */
    public function removeValue($name) {
        $this->checkState();
        unset($_SESSION[$name]);
    }

    /**
     * It gets the session id
     *
     * @return mixed, the session id
     */
    public function getId(){
        $this->checkState();
        return session_id();
    }

    /**
     * It sets the session container
     *
     * @param ISessionContainer container to set
     * @return void
     */
    public function setContainer(ISessionContainer $container) {
        $this->container= $container;
    }

    /**
     * It dumps the session contains
     *
     * @return array
     */
    public function dump() {
        return $_SESSION;
    }

    /**
     * It checks the session state
     *
     * This method is called internally to ensure that the session is started before using it.
     * @return TRUE if the session is started
     */
    protected function checkState() {
        if (!$this->isStarted) {
            $this->start();
        }
        return TRUE;
    }
}
