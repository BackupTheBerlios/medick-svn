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

include_once('active/record/Field.php');

class FieldsAggregate implements IteratorAggregate {
    
    // container
    private $fields;
    // pk field @var Field
    private $pk_filed = NULL;
    // affected flag.
    private $affected = FALSE;
    
    /** constructor...*/
    public function __construct() {
        $this->fields = new ArrayObject();
    }

    /** add a new Field on the fields container */
    public function add(Field $field) {
        if (!$this->contains($field)) $this->fields[] = $field;
        if ($field->isPk) $this->pk_field = $field;
    }
    
    /** check if the container contains the Field */
    public function contains(Field $field) {
        for ( $it = $this->getIterator(); $it->valid(); $it->next() ) {
            if ($it->current()->getName() == $field->getName()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /** it gets the iterator */
    public function getIterator() {
        return $this->fields->getIterator();
    }

    /** set the affected modifier */
    public function setAffected($affected) {
        $this->affected = (bool)$affected;
    }

    /** has affected fields by this run */
    public function hasAffected() {
        return $this->affected;
    }

    /**
     * it gets the primary key filed
     * @return Field, the field containing the pk.
     */
    public function getPrimaryKey() {
        return $this->pk_field;
    }
    
    /** It gets an array with the names of the affected fields */
    public function getAffectedFieldsNames() {
        $names= array();
        for ($it = $this->getIterator(); $it->valid(); $it->next()) {
            if ($it->current()->isAffected) {
                $names[] = $it->current()->getName();
            }
        }
        return $names;
    }

    /** get an array of objects Field[] that are affected(changed) by this run*/
    public function getAffectedFields() {
        if (!$this->affected) return array();
        $affected_fields = array();
        for ( $it = $this->getIterator(); $it->valid(); $it->next() ) {
            if ($it->current()->isAffected) {
                $affected_fields[] = $it->current();
            }
        }
        return $affected_fields;
    }
}

