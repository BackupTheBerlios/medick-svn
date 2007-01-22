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

// {{{ MedickException
/**
 * Our base Exception Class
 * 
 * @package medick.core
 */
class MedickException extends Exception {

    /**
     * Additional Informations provided by this Exception
     * @var string
     * @since Rev. 272
     */
    protected $userInfo= FALSE;

    /**
     * Create a new MedickException
     *
     * @param string message the message.
     * @param string userInfo additional user informations, defaults to empty string
     * @param int code error code, defaults to 0
     */
    public function __construct($message, $userInfo='', $code = '0') {
        if ($userInfo != '') {
            $this->userInfo = $userInfo;
        }
        parent::__construct($message, $code);
    }

    /**
     * Returns the additional / debug information for this error.
     *
     * @return string.
     */
    public function getUserInfo() {
        return $this->userInfo;
    }

}
// }}}
// {{{ Error
/**
 * Error, a routine error.
 * @package medick.core
 */
class Error extends MedickException {
    public function __construct($message, $code, $file, $line, $trace) {
        parent::__construct($message);
        $this->code  = $code;
        $this->file  = $file;
        $this->line  = $line;
        $this->trace = $trace;
    }
}
// }}}
// {{{ InvalidOffsetException
/**
 * Exception thrown when trying to acces an array by an invalid identifier(offset)
 * @package medick.core
 */
class InvalidOffsetException extends MedickException { }
// }}}
// {{{ InvalidArgumentException
if (!class_exists('InvalidArgumentException')) {
    /**
     * Exception that denotes invalid arguments were passed
     * @package medick.core
     */
    class InvalidArgumentException extends MedickException {    }
}
// }}}
// {{{ IOException
/**
 * General Input/Output Exception
 * @package medick.core
 * @subpackage io
 */
class IOException extends MedickException { }
// }}}
// {{{ FileNotFoundException
/**
 * Indicates that a file could not be found.
 * @package medick.core
 * @subpackage io
 */
class FileNotFoundException extends IOException { }
// }}}
// {{{ InjectorException
/**
 * It signals a problem with the Injector.
 * @package medick.action.controller
 */
class InjectorException extends MedickException { }
// }}}
// {{{ RouteException
/**
 * Exception that occurrs when a problem on the route is found.
 * @package medick.action.controller
 * @subpackage routing
 */
class RoutingException extends MedickException {    }
// }}}
// {{{ IllegalStateException
/**
 * Indicates an Illegal State of an Object (eg: when trying to use a Session before calling the start method)
 * @package medick.core
 */
class IllegalStateException extends MedickException {    }
// }}}
// {{{ ConfiguratorException
/**
 * It indicates a Cofigurator Exception
 * @package medick.configurator
 */
class ConfiguratorException extends MedickException {       }
// }}}
// {{{ LoggingException
/**
 * Logging Exception
 * @package medick.logger
 */
class LoggingException extends MedickException {       }
// }}}
// {{{ ActiveRecordException
/**
 * @package medick.active.record
 */
class ActiveRecordException extends MedickException {     }
// }}}
// {{{ RecordNotFoundException
/**
 * @package medick.active.record
 */
class RecordNotFoundException extends ActiveRecordException { }
// }}}
// {{{ AssociationNotFoundException
/**
 * @package medick.active.record
 * @subpackage association
 * @since Rev. 272
 */
class AssociationNotFoundException extends MedickException{ }
// }}}

