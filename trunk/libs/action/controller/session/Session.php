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
 
include_once('action/controller/session/SessionException.php');
include_once('action/controller/session/ISession.php');

/**
 * @package locknet7.action.controller.session
 */

class Session {
    
    /**
     * @var $instance object, this session instance
     */
    static private $instance=null;
    
    /**
     * Constructor.
     *
     * Just hide him
     */
    private function __construct() { }

    public function regenerateId() {
        session_regenerate_id();
    }
    
    /**
     * Sets a session value for the specified name
     *
     * @param $name, string the name of this session object
     * @param $value, string the value
     */
    public function setAttribute($name,$value) {
        return $_SESSION[$name] = $value;
    }
    
    public function removeAttribute($name) {
        $_SESSION[$name] = null;
        unset($_SESSION[$name]);
    }

    public function getAttribute($name) {
        return session_is_registered($name) ? $_SESSION[$name] : false;
    }
    
    public function getId(){
        return session_id();
    }
    
    public function dump() {
        return $_SESSION;
    }
    
    /**
     * Attempts to return a concrete Session instance based on
     * $driver.
     *
     * @param optional string $driver The type of concrete Session
     *                                subclass to return. The is based on the
     *                                driver ($driver). The code is
     *                                dynamically included.
     * @param optional array $params  A hash containing any additional
     *                                configuration or connection parameters
     *                                a subclass might need.
     *
     * @return mixed    The newly created concrete Session instance, or
     *                  false on an error.
     */
    private static final function factory($driver='', $params = array())
    {
        $drivers = array('Creole','PDO','File');
        
        if( ($driver=='') OR (!in_array($driver,$drivers)) ) {
            $driver = 'Default';
        }
        
        $class = $driver . 'Session';
        $file  = 'Session/driver/' . $class  . '.php';
        
        include_once($file);
        
        if (class_exists($class)) {
            return new $class($params);
        } else {
            throw new SessionException("Class " . $class . " don`t exist!");
        }
    }

    /**
     * Attempts to return a reference to a concrete Session
     * instance based on $driver. It will only create a new instance
     * if no Session instance with the same parameters
     * currently exists.
     *
     * This method must be invoked as: $var = Session::getInstance()
     *
     * @param string $driver          See Session::factory().
     * @param optional array $params  See Session::factory().
     *
     * @return mixed  The created concrete Session instance, or false
     *                on error.
     */
    public static final function getInstance($driver, $params = null)
    {
        $signature = serialize(array($driver, $params));
        if(!isset(self::$instance[$signature])){
            self::$instance[$signature] = self::factory($driver, $params);
        }
        return self::$instance[$signature];
    }
}
