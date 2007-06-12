<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
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
//////////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * It's a HTTPCookie 
 *
 * @package medick.action.controller
 * @subpackage http
 * @author Aurelian Oancea
 */

class Cookie extends Object {
    
    /** @var string
        Cookie name */ 
    private $name;

    /** @var string
        Cookie value */ 
    private $value;

    /** @var int
        Cookie expire */ 
    private $expire;
    
    /** @var string
        Cookie path */ 
    private $path;
    
    /** @var string 
        Cookie domain */
    private $domain;

    /** @var bool
        Cookie secure */ 
    private $secure;
    
    /**
     * Creates A new Cookie
     *
     * @param string Cookie name
     * @param string Cookie value
     * @param int 
     * @param string Cookie path
     * @param string Cookie domain
     * @param bool    
     */ 
    public function Cookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = FALSE) {
        $this->name   = $name;
        $this->value  = $value;
        $this->expire = $expire;
        $this->path   = $path;
        $this->domain = $domain;
        $this->secure = $secure;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value= $value;
    }

    public function getExpire() {
        return $this->expire;
    }

    public function setExpire($expire) {
        $this->expire = $expire;
    }
    
    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path= $path;
    }
    
    public function getDomain() {
        return $this->domain;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }
    
    public function getSecure() {
        return (bool)$this->secure;
    }

    public function setSecure($secure) {
        $this->secure= (bool)$secure;
    }
    
    public function toString() {
      return (
        $this->name . '=' . 
        ($this->value === '' ? 'deleted' : $this->value).
        ($this->expire !== 0 ? '; expires=' . gmdate('D, d-M-Y H:i:s \G\M\T', $this->expire) : '').
        ($this->path !== '' ? '; path=' . $this->path : '').
        ($this->domain !== '' ? '; domain=' . $this->domain : '').
        ($this->secure ? '; secure' : '')
      );
    }
}
