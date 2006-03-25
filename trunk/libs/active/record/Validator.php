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

/**
 * Active Record Validator
 *
 * In medick, validations is performed in ActiveRecord with the help
 * of before_* filters
 * <code>
 *  class Person extends ActiveRecord {
 *      protected funtion before_save() {
 *          $this->validates->presence_of('name','first_name');
 *          $this->validates->uniqueness_of('phone_no');
 *          return TRUE; // don't forget to return true, otherwise this object will not be saved in DB!
 *      }
 *  [....]
 * </code>
 * Validators will not throw any exception, but the class methods 
 * will return a boolean value, false if the validation failed.
 * In the next release, Validators will be extenible, but until then, API will be provided on request.
 * @package medick.active.record
 * @author Oancea Aurelian
 */ 
class Validator extends Object {

    /** @var DatabaseRow
        current database row */
    private $row;

    /**
     * Creates a new validator
     *
     * @param DatabaseRow row
     */
    public function Validator(DatabaseRow $row) {
        $this->row= $row;
    }
    
    /**
     * Magic PHP method that performs our validation
     *
     * @param string method the method name we want to invoke on this class
     * @param array args the method arguments
     * @return bool, true if this validation has errors
     */ 
    public function __call ($method, $args) {
        $has_errors= FALSE;
        foreach ($args as $argument) {
            if ($field = $this->row->getFieldByName($argument)) {
                if ($method == "presence_of") {
                    $has_errors = $this->isEmpty($field);
                } elseif ($method == "uniqueness_of" ) {
                    $has_errors = $this->isNotUnique($field);
                } else {
                    trigger_error('No such method validation method:' . $method, E_USER_ERROR);
                }
            } else {
                // exception?
                trigger_error('No such field to validate:' . $argument, E_USER_ERROR);
            }
        }
        return $has_errors;
    }
    
    /**
     * It checks if the given field is empty
     *
     * @param Field field
     * @return bool TRUE if is empty
     */ 
    private function isEmpty(Field $field) {
        if ($field->getValue() == '') {
            $field->addError('is empty');
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Check if the given filed is not unique
     *
     * Executes:
     * <code>
     *  SELECT ${FIELD.NAME} FROM ${TABLE} WHERE ${FIELD.NAME}=${FIELD.VALUE} AND ${ROW.PRIMARY_KEY.NAME}!=${ROW.PRIMARY_KEY.VALUE}
     * </code>
     * If the result set is empty, we return FALSE, otherwise this Filed is unique and therefore we return TRUE
     *
     * @todo this should also work for fields without PK?
     * 
     * @param Filed field
     * @return bool TRUE if is not unique
     */ 
    private function isNotUnique(Field $field) {
        $supp= $this->row->getPrimaryKey()->getValue()===NULL ? '' : ' and ' . $this->row->getPrimaryKey()->getName() . '!=?';
        try {
            ActiveRecord::build(
                new QueryBuilder(
                    Inflector::singularize($this->row->getTable()), 
                    array(
                        'first', 
                        array('condition'=>$field->getName() . '=?' . $supp,
                              'columns'=>$field->getName()
                    ), 
                        array($field->getValue(), $this->row->getPrimaryKey()->getValue()
                    )
                )
            ));
            $field->addError('is not unique');
            return true;
        } catch (RecordNotFoundException $rnfEx) {
            return false;
        }
    }

}

