<?php
//
// $Id: Cookie.php 444 2007-07-20 17:57:43Z aurelian $
//

/**
 * It's a HTTPCookie 
 *
 * @package medick.action.controller
 * @subpackage http
 * @author Aurelian Oancea
 */

class HTTPCookie extends Object {
    
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

