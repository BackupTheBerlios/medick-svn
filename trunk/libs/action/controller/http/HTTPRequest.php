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

include_once('action/controller/http/Cookie.php');
include_once('action/controller/session/Session.php');

/**
 * A HTTPRequest
 *
 * @package medick.action.controller
 * @subpackage http
 * @author Aurelian Oancea
 */
class HTTPRequest extends Request {

    private $method;
    
    /** @var Session */
    private $session;

    /** @var string
        path_info_parts */
    private $requestUri= NULL;

    /** @var array
        the list of headers associated with this HTTPRequest */
    private $headers= array();

    /** @var array
        cookies list */
    private $cookies= array();
    
    /**
     * Constructor.
     * It builds the HTTPRequest object
     *
     * @todo a URI Helper should be written.
     * @todo a Cookie class should be written.
     */
    public function HTTPRequest() {
        $this->method= isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        foreach (array_merge($_GET,$_POST) as $key=>$value) {
            $this->setParameter($key, $value);
        }

        foreach ($_COOKIE as $cookie_name=>$cookie_value) {
            $this->cookies[$cookie_name]= new Cookie($cookie_name, $cookie_value);
        }
        
        unset($_REQUEST); unset($_GET); unset($_POST);

        if (array_key_exists('PATH_INFO', $_SERVER) && $_SERVER['PATH_INFO']!='' ) {
            $this->requestUri= $_SERVER['PATH_INFO'];
        }
        // TODO:
        //      -> this is for php as cgi, or where PATH_INFO is not available
        //      -> ORIG_PATH_INFO should also work
        //      -> should substract only the documnet root
        elseif (array_key_exists('REQUEST_URI', $_SERVER)) {
            $this->requestUri= substr($_SERVER['REQUEST_URI'],7);
        }

        $this->session = new Session();
        $this->headers = HTTPRequest::getAllHeaders();
    }

    /**
     * Get the current request method
     *
     * @return string can be POST or GET
     */ 
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * Check if this request was made using POST
     *
     * @return bool true if it's a POST
     */ 
    public function isPost() {
        return $this->method == 'POST';
    }
    
    /**
     * Check if this Request was made using GET
     *
     * @return bool true if it was GET
     */ 
    public function isGet() {
        return $this->method == 'GET';
    }
    
    /**
     * Check if this Request was made with an AJAX call (Xhr)
     *
     * @return bool true if it was Xhr
     */ 
    public function isXhr() {
      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }    
    
    /**
     * Gets an array of Cookies
     *
     * @return array
     */ 
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * Check if it has a Cookie with the specfied name
     *
     * @param string the Cookie name
     * @return bool true if it has
     */ 
    public function hasCookie($name) {
        return isset($this->cookies[$name]);
    }

    /**
     * It gets a cookie by it's name
     *
     * @param string cookie name
     * @return Cookie or FALSE if this Request don't have the requested cookie
     */ 
    public function getCookie($name) {
        return $this->hasCookie($name) ? $this->cookies[$name] : FALSE;
    }
    
    /**
     * It gets an array of headers associated with this request
     *
     * @return array
     */ 
    public function getHeaders() {
        return $this->headers;
    }
    
    /**
     * It gets a header
     * 
     * @param strign name of the header to look for
     * @return string header value or FALSE if it don't have the header
     */ 
    public function getHeader($name) {
        return $this->hasHeader($name) ? $this->headers[ucfirst($name)] : FALSE;
    }
    
    /**
     * Check if it has a specific header
     *
     * @param string name of the header to check for
     * @return bool true if it has
     */ 
    public function hasHeader($name) {
        return isset($this->headers[ucfirst($name)]);
    }

    /**
     * Sets this Request URI
     *
     * Usefull for testing
     * @param uri string incoming URI
     * @return void
     */
    public function setRequestUri($uri) {
      $this->requestUri= $uri;
    }

    /**
     * It gets a part of the path info associated with this request
     *
     * @return value of this part or NULL if this part is not defined
     */
    public function getRequestUri() {
        return $this->requestUri;
    }

    public function getUriParts() {
        if (is_null($this->requestUri)) return array();
        return explode('/', trim($this->requestUri,'/'));
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
    // public function getRequestURI() {  }
    public function getProtocol() {  }
    // }}}

    /**
     * A wrapper around getallheaders apache function that gets a list
     * of headers associated with this HTTPRequest.
     *
     * @return array
     */
    protected static function getAllHeaders() {
        $headers= array();
        if (function_exists('getallheaders')) {
            // this will work only for mod_php
            $headers= getallheaders();
        } else {
            foreach($_SERVER as $header=>$value) {
                if(ereg('HTTP_(.+)',$header,$hp)) {
                    $headers[ucfirst(strtolower($hp[1]))] = $value;
                }
            }
        }
        return $headers;
    }

}
