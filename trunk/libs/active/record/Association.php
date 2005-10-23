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
 * Association base abstract class
 * @package locknet7.active.record
 */

abstract class Association extends Object {

    public $owner= NULL;

    public $class= NULL;

    public $pk   = NULL;

    abstract public function execute();

    protected function pre_execution() {
        ActiveRecordBase::setTable(Inflector::pluralize($this->class));
    }

    protected function post_execution() {
        ActiveRecordBase::setTable(Inflector::pluralize($this->owner));
    }
}

/**
 * HasAndBelongsToManyAssociation
 * @package locknet7.active.record.association
 */
class HasAndBelongsToManyAssociation extends Association {
    public function execute() {
        $this->pre_execution();
        $join_table= Inflector::pluralize($this->class) . '_' . Inflector::pluralize($this->owner);
        $ret= ActiveRecordBase::__find(
                            array(
                                array(
                                    'include'  => Inflector::pluralize($this->class) . '.*',
                                    'left join'=>
                                              $join_table . ' ON ' .
                                              Inflector::pluralize($this->class) .
                                              '.id=' . $join_table . '.' . $this->class . '_id',
                                     'condition'=> $join_table . '.' . $this->owner . '_id=' . $this->pk
                                    )
                                )
                            );
        $this->post_execution();
        return $ret;
    }
}
