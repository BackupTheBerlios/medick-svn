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
 * Its a Route Parameter.
 * @package locknet7.action.controller.route
 */
class RouteParam extends Object {

    /** @var string param name */
    private $name;
    /** @var string param value */
    private $value;
    /** XXX. NotUsed */
    private $type;
    /** @var IValidators[] param validators */
    private $validators;

    /**
     * Creates a new RouteParam object
     * @param string name of this Route Parameter
     */
    public function __construct($name) {
        $this->name = $name;
        $this->validators = array();
    }

    /** XXX. NotUsed.
     * Sets this parameter Type
     * @param string type
     */
    public function setType($type) {
        $this->type= $type;
    }

    /** XXX. NotUsed
     * It gets this param type
     * @return string type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Adds a validator for this parameter
     * @param IValidator validators
     */
    public function add(IValidator $validator) {
        if ($this->contains($validator)) return;
        $this->validators[]= $validator;
    }
    
    /**
     * It gets the list of Validators
     * @return IValidators[]
     */
    public function getValidators() {
        return $this->validator;
    }
    
    /**
     * Check if this parameter hav attached Validators
     * @return bool, TRUE if the parameter has at least one IValidator attached, FALSE otherwise
     */
    public function hasValidators() {
      return sizeof($this->validators) > 0;
    }
    
    /** XXX. NotDone.
     * Check if the parameter contains the given Validator
     * @param IValidator validator to check if is already in the stack of IValidators elements
     * @return IValidator if this parameter has the IValidator attached or FALSE if the stack of IValidator dont contains this IValidator
     */
    public function contains (IValidator $validator) {
        foreach ($this->validators AS $_validator) {

        }
        return FALSE;
    }

    /**
     * It set`s the value of this parameter.
     * @param string value
     */
    public function setValue($value) {
        $this->value= $value;
    }

    /**
     * It gets the value of this parameter
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * It gets the name of this parameter
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}

