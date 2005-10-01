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
 * @package locknet7.medick
 */
 
class Registry extends Object {

    /** we want only one instance of the Registry */
    private final function __construct() {   }
    private final function __clone() {   }
    
    // {{{ static
    
    /** @var array registry database */
    private static $registry= array();

    /** 
     * put an Object into Registry database
     * @param Object the Object instance to add into registry
     * @param string key
     * @return Object, the Object just added.
     */
    public static function put(Object $obj, $key) {
        self::$registry[$key]= $obj;
        return $obj;
    }
    
    /**
     * It gets an Object from the registry database
     * @param string key, the object identifier
     * @return Object
     */
    public static function get($key) {
        return self::$registry[$key];
    }
    
    /**
     * It gets the Registry instance
     * @return Registry
     */
    public static function getInstance() {
        if (!isset(self::$registry['__registry'])) {
            self::$registry['__registry']= new Registry();
        }
        return self::$registry['__registry'];
    }
    // }}}
}

