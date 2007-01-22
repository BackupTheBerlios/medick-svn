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

/**
 * It represents a field from DB
 *
 * @package medick.active.record
 * @author Oancea Aurelian
 */
class Field extends Object {

    /** @var string
        name of the field as it is in DB */
    private $name;

    /**
     * Formatted field name, _ is replaced by spaces
     * @var string
     * @since Rev. 272
     */
    private $formattedName;

    /** @var mixed 
        value of the field */
    private $value;

    /** @var string
        field sql type */
    public $type;

    /** @var bool
        is primary key flag */
    public $isPk = FALSE;

    /** @var bool
        is foreign key flag */
    public $isFk = FALSE;

    /** @var bool
        if this field was affected by the current run */
    public $isAffected = FALSE;

    /** @var string
        the foreign key table */
    public $fkTable;

    /** @var array
        a list of erros associated with this field */
    protected $errors;

    /**
     * Creates a new Field Object
     *
     * @param string the name of this Field
     */
    public function Field($name) {
        $this->name = $name;
        $this->errors= array();
        $this->formattedName= Inflector::humanize($name);
    }

    public function getFormattedName() {
        return $this->formattedName;
    }

    public function setFormattedName($formattedName) {
        $this->formattedName= $formattedName;
    }

    /**
     * Adds an error to this field
     *
     * @param string message, the error message
     * @return void
     */
    public function addError($message) {
        $this->errors[]=$message;
    }

    /**
     * It gets the errors associated with this field
     *
     * @return array the list of errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if this field has errors
     *
     * @return bool, TRUE if it has errors
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }

    /**
     * It gets the name of this field
     *
     * @return string name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * It gets the value of this Field
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * It sets the value of this Field
     *
     * @param mixed value
     */
    public function setValue($value) {
        $this->value = $value;
        $this->isAffected= TRUE;
    }

    /**
     * Returns this field value
     *
     * @return mixed
     */
    public function toString() {
        return $this->value;
    }
  
    /**
     * Returns the number of chars for this Field
     *
     * It's a <tt>strlen</tt> of the Field value
     *
     * @return int
     */
    public function length() {
        return strlen($this->value);
    }
    
    /**
     * Alias of htmlentities php function
     * 
     * <code>
     *    $article= new Article();
     *    $article->description= "A 'quote' is <b>bold</b>";
     *    $article->description->h(); // outputs: A 'quote' is &lt;b&gt;bold&lt;/b&gt;
     *    $article->description->h(ENT_QUOTES); // outputs: A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;
     * </code>
     *
     * @param int quote_style defaults to ENT_COMPAT
     * @param string charst defaults to ISO-8859-1
     * @see http://php.net/htmlentities
     * @return string 
     */
    public function h($quote_style=ENT_COMPAT, $charset='ISO-8859-1') {
        return htmlentities($this->value, $quote_style, $charset);
    }
    
}
