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
 * A logged event object
 * @package locknet7.logger
 */

class LoggingEvent extends Object {
    /** @var array */
    public $backtrace = array();
    /** @var mixed */
    public $message;
    /** @var int */
    public $level;
    /** @var string */
    public $file;
    /** @var int */
    public $line;
    /** @var string */
    public $function;
    /** @var string */
    public $class;
    /** @var string */
    public $date;
    
    /**
     * Constructor, set`s the properties
     * @param mixed, message, the message
     * @param string, this event level
     */ 
    public function __construct($message, $level) {
        $this->backtrace = debug_backtrace();
        $this->message   = $message;
        $this->level     = strtoupper($level);
        $this->date      = time();
        $this->file      = end(@explode(DIRECTORY_SEPARATOR,@$this->backtrace[1]['file']));
        $this->line      = @$this->backtrace[2]['line'];
        $this->class     = @$this->backtrace[3]['class'];
        $this->function  = @$this->backtrace[3]['function'];
    }
}

