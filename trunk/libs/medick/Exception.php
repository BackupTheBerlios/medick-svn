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

// {{{ MedickException
/**
 * Our base Exception Class
 * @package locknet7.medick
 */
class MedickException extends Exception {

    /**
     * Create a new MedickException
     * @param string the message.
     * @param int code.
     */
    public function __construct($message, $code = '0') {
        parent::__construct($message, $code);
    }
}
// }}}
// {{{ Error
/**
 * Error, a routine error.
 * @package locknet7.medick
 */
class Error extends MedickException {
    public function __construct($message, $code, $file, $line, $trace) {
        parent::__construct($message, $code);
        $this->file  = $file;
        $this->line  = $line;
        $this->trace = $trace;
    }
}
// }}}
// {{{ InvalidOffsetException
/**
 * Exception thrown when trying to acces an array by an invalid identifier(offset)
 * @package locknet7.medick
 */
class InvalidOffsetException extends MedickException { }
// }}}
// {{{ IOException
/**
 * General Input/Output Exception
 * @package locknet7.medick.io
 */
class IOException extends MedickException { }
// }}}
// {{{ FileNotFoundException
/**
 * Indicates that a file could not be found.
 * @package locknet7.medick.io
 */
class FileNotFoundException extends IOException { }
// }}}
// {{{ InjectorException
/**
 * It signals a problem with the Injector.
 * @package locknet7.action.controller
 */
class InjectorException extends MedickException { }
// }}}
// {{{ RouteException
/**
 * Exception that occurrs when a problem on the route is found.
 * @package locknet7.action.controller.route
 */
class RouteException extends MedickException {    }
// }}}
// {{{ CLIException
/**
 * @package locknet7.action.controller.request
 */
class CLIException extends MedickException {      }
// }}}
// {{{ IllegalStateException
class IllegalStateException extends MedickException {    }
// }}}
// {{{ ConfiguratorException
/**
 * Cofigurator Exception
 * @package locknet7.config
 */
class ConfiguratorException extends MedickException {       }
// }}}
// {{{ LoggingException
/**
 * Logging Exception
 * @package locknet7.logger
 */
class LoggingException extends MedickException {       }
// }}}
// {{{ ActiveRecordException
/**
 * @package locknet7.active.record
 */
class ActiveRecordException extends MedickException {     }
// }}}
// {{{ RecordNotFoundException
/**
 * @package locknet7.active.record
 */
class RecordNotFoundException extends ActiveRecordException { }
// }}}

