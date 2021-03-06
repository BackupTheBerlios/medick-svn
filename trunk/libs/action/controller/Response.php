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
 * It is a Response that a medick application will always try to build.
 * 
 * A Dispatcher will know how to dump the buffer of this response back to the user.
 * 
 * @package medick.action.controller
 * @author Aurelian Oancea
 */
class Response extends Object {
    
    /** @var string
        response content */
    protected $content;

    /**
     * Hidden Constructor
     *
     * This class is not meant to be instanciated,
     * insteard use inheritance to extend and build more specialized Responses
     *
     * @see HTTPResponse
     */
    protected function Response(){  }

    /**
     * Set the content
     * Will discard all the changes made on the buffer so far
     * 
     * @param mixed content, the content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /** 
     * Add content on the buffer
     *
     * @param mixed content
     */
    public function append($content) {
        $this->content .= $content;
    }

    /** 
     * It gets the content
     * @return string the content that we push so far on to this Response
     */
    public function getContent() {
        return $this->content;
    }

    /** 
     * Echos the content (buffer)
     */
    public function dump() {
        echo $this->content;
    }

}
