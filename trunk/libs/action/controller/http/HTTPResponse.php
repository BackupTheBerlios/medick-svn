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
 * @package locknet7.action.controller.response
 */

class HTTPResponse implements IResponse {

    /** Page content */
    private $content;
    
    /** 
     * Sets the header $name with $value 
     * @param string, name the name of the header
     * @param mixed, value, the value of this header
     */
    public function setHeader($name,$value) {
        header($name . ": " . $value);
    }

    /**
     * Sets the content type header
     * @param strign type, the content type
     */
    public function setContentType($type) {
        return $this->setHeader('Content-type',$type);
    }

    /**
     * @see Response::setStatus($status)
     */
    public function setStatus($status) {
        switch($status){
            case self::SC_OK: // 200
                $message = 'OK';
                break;
            case self::SC_NOT_MODIFIED: // 304
                $message = 'Not Modified';
                break;
            case self::SC_NOT_FOUND: // 404
                $message = 'Not Found';
                break;
            case self::SC_INTERNAL_SERVER_ERROR: // 500
                $message = 'Internal Server Error';
                break;
            default:
                return;
        }
        header("HTTP/1.1 " . $status . $message, TRUE, $status);
    }

    public function redirect($location) {
        $this->setHeader('Location', $location);
        $this->content = "<html><body>You are being <a href=\"$location\">redirected</a>.</body></html>";
    }
    
    /**
     * Sets body content 
     * @param string body, body content
     */
    public function setContent($content) {
        $this->content = $content;
    }
    
    /** It gets the body content */
    public function getContent() {
        return $this->content;
    }
    
    /** echo`s the body */
    public function dump() {
        echo $this->content;    
    }

}
