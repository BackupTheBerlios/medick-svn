<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
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
 * HTTP Response Object 
 *
 * @todo: think of a cachable list of headers and how to send them.
 *
 * Caution: only 4 response statuses are implemented in Response::setStatus method:
 *
 * 200 OK, 304 Not Modified, 404 Not Found, 500 Internal Server Error
 *
 * Contact me if you need more.
 *
 * @package medick.action.controller
 * @subpackage http
 * @author Aurelian Oancea
 */
class HTTPResponse extends Response {

    /** Status code (200) indicating the request succeeded normally. */
    const SC_OK = 200;

    /** Status code (304) indicating that a conditional GET
        operation found that the resource was available and not modified. */
    const SC_NOT_MODIFIED = 304;

    /** Status code (400) indicating the request sent by the
        client was syntactically incorrect. */
    const SC_BAD_REQUEST = 400;

    /** Status code (403) indicating the server
        understood the request but refused to fulfill it. */
    const SC_FORBIDDEN = 403;

    /** Status code (404) indicating that the requested
        resource is not available. */
    const SC_NOT_FOUND = 404;

    /** Status code (500) indicating an error inside
        the HTTP server which prevented it from fulfilling the request. */
    const SC_INTERNAL_SERVER_ERROR = 500;

    /** Status code (503) indicating that the HTTP server
        is temporarily overloaded, and unable to handle the request. */
    const SC_SERVICE_UNAVAILABLE = 503;

    /** Constructor */
    public function HTTPResponse() {  }
    
    /**
     * Sets the header $name with $value
     *
     * Caution: this method makes use of PHP function header() directly, meaning that 
     * there is no instance variable to keep a list of all the headers that are build 
     * during a Response sequence.
     *
     * <code>
     *  $response->setHeader("X-Foo", "Bar");// <==> header("X-Foo: Bar");
     * </code>
     *
     * @param string the name of the header
     * @param mixed  the value of this header
     */
    public function setHeader($name, $value) {
        header($name . ": " . $value);
    }

    /**
     * It gets the Response headers
     *
     * It gets only the headers that have been already sent, 
     * eg. Content-Type header will not be on the list
     *
     * Caution: this method is not cachable, meaning that multiple calls on it
     * will fetch the list of headers each time. There is no $headers instance 
     * variable to keep a list that will be later sent with the Response.
     *
     * Caution: names and values are trimmed!
     *
     * <code>
     * $response->setHeader("X-Foo", "Bar");
     * $response->setHeader("X-Framework", "Medick "); // notice the space at the end
     * $response->getHeaders(); // array("X-Foo"=>"Bar", "X-Framework"=>"Medick");
     * </code>
     */ 
    public function getHeaders() {
      $headers= array();
      foreach(headers_list() as $header) {
        list($name, $value) = explode(':', $header);
        $headers[trim($name)]= trim($value);
      }
      return $headers;
    }

    public function getHeader( $name ) {
      $headers= $this->getHeaders();
      return isset($headers[trim($name)]) ? trim($headers[trim($name)]) : false;
    }
    
    // bleah.
    public function hasHeader( $name ) {
      return in_array(trim($name), array_keys($this->getHeaders()));
    }

    /**
     * Sets the content-type header
     *
     * @param string the content type
     */
    public function setContentType($type) {
        return $this->setHeader('Content-type', $type);
    }
    
    /**
     * Sets a Cookie.
     * 
     * <code>
     *  $response->setCookie(new Cookie("Foo", "Bar"));
     * </code>
     *
     * @param Cookie Cookie
     * @see Cookie
     */ 
    public function setCookie(Cookie $cookie) {
        $this->setHeader('Set-Cookie', $cookie->toString());
    }
    
    /**
     * Sets the status of this response
     *
     * @param HTTPResponse::SC_* the status of this response
     */
    public function setStatus( $status ) {
        switch($status){
            case HTTPResponse::SC_OK: // 200
                $message = 'OK';
                break;
            case HTTPResponse::SC_NOT_MODIFIED: // 304
                $message = 'Not Modified';
                break;
            case HTTPResponse::SC_NOT_FOUND: // 404
                $message = 'Not Found';
                break;
            case HTTPResponse::SC_INTERNAL_SERVER_ERROR: // 500
                $message = 'Internal Server Error';
                break;
            default:
                return;
        }
        header("HTTP/1.1 " . $status . $message, TRUE, $status);
    }

    /**
     * Perform a HTTP redirect
     *
     * @param string location, location to redirect to
     */
    public function redirect( $location ) {
        $this->setHeader('Location', $location);
        $this->content = '<html><body>You are being <a href="'.$location.'">redirected</a>.</body></html>';
    }
}

