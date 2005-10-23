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

/**
 * @package locknet7.active.record
 */

class FieldsAggregate extends Object implements IteratorAggregate {
    
    /** @var ArrayObject
        fields container */
    private $fields;
    
    /** @var Field
        pk field*/
    private $pk_filed = NULL;
    
    /** @var bool 
        affected flag. */
    private $affected = FALSE;
    
    /**
     * FieldsAggregate Constructor
     */
    public function __construct() {
        $this->fields = new ArrayObject();
    }

    /** 
     * Add a new Field on the fields container 
     */
    public function add(Field $field) {
        if (!$this->contains($field)) $this->fields[] = $field;
        if ($field->isPk) $this->pk_field = $field;
        return $field;
    }
    
    /** 
     * Check if the container contains the given Field
     * @return bool, TRUE if it contains this Field, FALSE otherwise
     */
    public function contains(Field $field) {
        for ( $it = $this->getIterator(); $it->valid(); $it->next() ) {
            if ($it->current()->getName() == $field->getName()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /** 
     * It gets the iterator
     * @return Iterator
     */
    public function getIterator() {
        return $this->fields->getIterator();
    }

    /** 
     * Set the affected modifier 
     * @param bool affected
     */
    public function setAffected($affected) {
        $this->affected = (bool)$affected;
    }

    /** 
     * It checks if it has affected fields by this run
     * @return bool TRUE if it has, FALSE otherwise
     */
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
    
    /** 
     * It gets an array with the names of the affected fields 
     * @return array
     */
    public function getAffectedFieldsNames() {
        $names= array();
        for ($it = $this->getIterator(); $it->valid(); $it->next()) {
            if ($it->current()->isAffected) {
                $names[] = $it->current()->getName();
            }
        }
        return $names;
    }

    /** 
     * Get an array of objects Field[] that are affected(changed) by this run
     * @return Field[]
     */
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
