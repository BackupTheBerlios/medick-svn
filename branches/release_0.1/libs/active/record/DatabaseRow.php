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

include_once('active/record/Field.php');

/**
 * It represents a Row from the Database
 * 
 * @package locknet7.active.record
 */

class DatabaseRow extends Collection {

    /** @var Field
        pk field */
    private $pk_filed = NULL;

    /** @var array
        holds the field names */
    private $field_names = array();

    /** @var array
        affected fields, Filed[] */
    private $affected_fields = array();

    /** @var bool
        affected flag. */
    private $affected = FALSE;

    /** @var string
        this database table name */ 
    private $table;

    /**
     * Creates a new DatabaseRow
     *
     * @param string table, the table name where this row is from
     */ 
    public function DatabaseRow($table) {
        $this->table= $table;
        parent::__construct();
    }

    /**
     * It gets the table name
     *
     * @return string, the table name
     */ 
    public function getTable() {
        return $this->table;
    }

    /**
     * Automatic trigger executed when a new Field is added on to this Collection
     * @see Collection::onAdd
     */
    public function onAdd(Object $field) {
        if (!$field instanceof Field) {
            throw new IllegalArgumentException(
                $field->getClassName() . ' wrong parameter type, it should be a Field object');
        }
        if ($field->isPk) $this->pk_field = $field;
        return TRUE;
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
     * It gets the primary key filed
     *
     * @return Field, the field containing the pk.
     */
    public function getPrimaryKey() {
        return $this->pk_field;
    }

    /**
     * It gets a filed by its name
     *
     * @param string field_name the field name to search for
     * @return Field or FALSE if there is no Field by the given name
     */
    public function getFieldByName($field_name) {
        $it = $this->iterator();
        while($it->hasNext()) {
            $current= $it->next();
            if ($current->getName() == $field_name) {
                return $current;
            }
        }
        return FALSE;
    }

    /**
     * Collects errors from the fields added on this row
     *
     * @return array an array of errors
     */ 
    public function collectErrors() {
        $errors= array();
        $it = $this->iterator();
        while($it->hasNext()) {
            $current= $it->next();
            if ($current->hasErrors()) {
                $errors[]= $current->getErrors();
            }
        }
        return $errors;
    }

    public function updateStatus(Field $field, $value) {
        $field->setValue($value);
        $field->isAffected = TRUE;
        $this->field_names[] = $field->getName();
        $this->affected_fields[] = $field;
        $this->affected = TRUE;
    }

    /**
     * It gets an array with the names of the affected fields
     * @return array
     */
    public function getAffectedFieldsNames() {
        return $this->field_names;
    }

    /**
     * Get an array of objects Field[] that are affected(changed) by this run
     * @return Field[]
     */
    public function getAffectedFields() {
        return $this->hasAffected() ? $this->affected_fields : array();
    }
}

