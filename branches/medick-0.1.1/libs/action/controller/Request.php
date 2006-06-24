<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
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

include_once('action/controller/http/HTTPRequest.php');

/**
 * It is an incoming request from the user
 * 
 * Contains various informations about the Request Parameters
 * @see HTTPRequest, Dispatcher, Route
 * @package medick.action.controller
 * @author Oancea Aurelian
 */
class Request extends Object {

    /** @var array
        current request parameters */
    private $params = array();

    /** 
     * Hidden Constructor 
     */
    protected function Request() {  }

    /**
     * It gets a Request Parameter
     * 
     * @param mixed, param, the paremeter name
     * @return the param value of NULL if this param was not passed with this Resuest
     */
    public function getParameter($param) {
        return $this->hasParameter($param) ? $this->params[$param] : NULL;
    }

    /**
     * Check if the current Request has the parameter with the specified name
     * 
     * @param string param_name the parameter name
     * @return bool TRUE if the parameter_name is included in this request, FALSE otherwise
     */
    public function hasParameter($param_name) {
        return isset($this->params[$param_name]);
    }

    /**
     * It gets all the parameters of this Request
     * 
     * @return array this request parameters.
     */
    public function getParameters() {
        return $this->params;
    }

    /**
     * It sets a Request Parameter
     *
     * @param string, name, the name of the param to set
     * @param mixed, value, value of the param
     * @return void
     */
    public function setParameter($name, $value) {
        $this->params[$name] = $value;
    }

    /**
     * It adds an array of parameters on this Request
     *
     * @param array parameters, parameters name/value pairs
     * @return void
     */ 
    public function setParameters(/*Array*/ $parameters=array()) {
        foreach ($parameters as $name=>$value) {
            $this->setParameter($name, $value);
        }
    }
    
    /**
     * Gets a string representation of this Object
     *
     * @return string
     */ 
    public function toString() {
        $buff = "{".$this->getClassName()."}-->";
        foreach ($this->getParameters() as $name=>$value) {
            $buff .= "[{$name}={$value}]";
        }
        return $buff;
    }
}

