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
 * Replacement for multiple singletons
 *
 * Registry is an object witch holds instances of other objects
 * 
 * @package medick.core
 * @author Oancea Aurelian
 */
class Registry extends Object {

    private final function __construct() {   }
    private final function __clone() {   }

    // {{{ static

    /** @var array 
        registry database */
    private static $registry= array();

    /**
     * Put an Object into Registry database
     * 
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
     * 
     * @param string key, the object identifier
     * @return Object
     * @throws InvalidOffsetException
     */
    public static function get($key) {
        if (isset(self::$registry[$key])) {
            return self::$registry[$key];
        }
        throw new InvalidOffsetException('Cannot access the object identified by key: `' . $key . '` from Registry Database!');
    }

    /**
     * Removes an Object from the Registry Database
     * 
     * @param string key, object identifier
     * @return Object, the object removed.
     */
    public static function remove($key) {
        $obj= self::get($key);
        unset(self::$registry[$key]);
        return $obj;
    }

    /**
     * Closes and clean-up the registry database
     */
    public static function close() {
        return self::$registry= array();
    }
    // }}}
}
