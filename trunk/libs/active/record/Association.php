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
 * Association base abstract class
 * @package locknet7.active.record
 */
abstract class Association extends Object {

    /** current object name */
    protected $owner= NULL;

    /** returning object name(to search for) */
    protected $class= NULL;

    /** primary key value */
    protected $pk   = NULL;

    /** */
    protected $fields;

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function setClass($class) {
        $this->class = $class;
    }

    public function setOwner($owner) {
        $this->owner = $owner;
    }

    public function setPk($pk) {
        $this->pk = $pk;
    }

    /** execute function */
    abstract public function execute();

    /** */
    protected function pre_execution() {
        return $this->reload($this->class);
    }

    /** */
    protected function post_execution() {
        return $this->reload($this->owner);
    }

    private function reload($what) {
        if ($this->getClassName() == 'HasAndBelongsToManyAssociation') {
            return ActiveRecordBase::initialize(Inflector::singularize($what));
        } else {
            return ActiveRecordBase::initialize($what);
        }
    }

    /**
     * Resolves this Association
     *
     * Returns a new instance of the solved Association
     * @return Association
     * @throws AssociationNotFoundException when we cannot resolve this Association
     * @since Rev. 272
     */
    public static function resolve($associations, $owner, $class, $fields) {
        if ( is_string($associations['has_one']) && preg_match("/$class/", $associations['has_one']) ) {
            $type= 'HasOneAssociation';
        } elseif( is_array($associations['has_one']) && in_array($class, $associations['has_one']) ) {
            $type= 'HasOneAssociation';
        } elseif( is_string($associations['belongs_to']) && preg_match("/$class/", $associations['belongs_to']) ) {
            $type= 'BelongsToAssociation';
        } elseif( is_array($associations['belongs_to']) && in_array($class, $associations['belongs_to']) ) {
            $type= 'BelongsToAssociation';
        } elseif( is_string($associations['has_many']) &&
                preg_match("/" . Inflector::singularize($class) . "/", $associations['has_many']) ) {
            $type= 'HasManyAssociation';
        } elseif( is_array($associations['has_many']) &&
                in_array(Inflector::singularize($class), $associations['has_many']) ) {
            $type= 'HasManyAssociation';
        } elseif( is_string($associations['has_and_belongs_to_many']) &&
                preg_match("/$class/", $associations['has_and_belongs_to_many']) ) {
            $type= 'HasAndBelongsToManyAssociation';
        } elseif( is_array($associations['has_and_belongs_to_many']) &&
                in_array($class, $associations['has_and_belongs_to_many']) ) {
            $type= 'HasAndBelongsToManyAssociation';
        } else {
            throw new AssociationNotFoundException ('Association not found.');
        }

        $association= new $type;
        $association->setOwner($owner);
        $association->setClass($class);
        $association->setFields($fields);
        $association->setPk($fields->getPrimaryKey()->getValue());
        return $association;
    }

}

/**
 * HasMany Association type
 *
 * It solves associations based on sql foreign keys
 * <code>
 *     class Author extends ActiveRecordBase {
 *         protected $has_many=array('articles'); // plural!
 *         // same as:
 *         // protected $has_many='articles';
 * </code>
 *
 * @package locknet7.active.record.association
 * @since Rev. 272
 */
class HasManyAssociation extends Association {

    public function execute() {
        $this->class = Inflector::singularize($this->class);
        $this->pre_execution();
        $ret = ActiveRecordBase::__find(array(
                array('condition'=>Inflector::singularize($this->owner) . '_id=' . $this->pk)));
        $this->post_execution();
        return $ret;
    }

}

/**
 * HasOne Association type
 *
 * It solves associations based on sql foreign keys
 * <code>
 *     class Article extends ActiveRecordBase {
 *         protected $has_one=array('author');
 *         // same as:
 *         // protected $has_one='author';
 * </code>
 *
 * @package locknet7.active.record.association
 * @since Rev. 272
 */
class HasOneAssociation extends Association {

    /**
     * It Executes this Association
     * @see Association#execute
     */
    public function execute() {
        $fk= $this->class.'_id'; // foreign key name: the class name+"_id" suffix"
        $it= $this->fields->iterator();
        while($it->hasNext()) {
            $current= $it->next();
            if ($current->getName() == $fk) {
                $this->pre_execution();
                $ret= ActiveRecordBase::__find(array($current->getValue()));
                $this->post_execution();
                return $ret;
            }
        }
    }
}

/**
 * BelongsToAssociation
 *
 * A Special case of HasOneAssociation
 * <code>
 *     class Article extends ActiveRecordBase {
 *         protected $belongs_to=array('author');
 *         // same as:
 *         // protected $belongs_to='author';
 * </code>
 *
 * @package locknet7.active.record.association
 * @since Rev. 272
 */
class BelongsToAssociation extends HasOneAssociation {    }

/**
 * HasAndBelongsToManyAssociation
 * @package locknet7.active.record.association
 */
class HasAndBelongsToManyAssociation extends Association {

    public function execute() {
        $this->pre_execution();
        if ($this->class < $this->owner) {
            $join_table= $this->class . '_' . Inflector::pluralize($this->owner);
        } else {
            $join_table= Inflector::pluralize($this->owner) . '_' . Inflector::pluralize($this->class);
        }
        $ret= ActiveRecordBase::__find(
                            array(
                                array(
                                    'include'  => $this->class . '.*',
                                    'left join'=>
                                        $join_table . ' ON ' . $this->class .
                                        '.id=' . $join_table . '.' . Inflector::singularize($this->class) . '_id',
                                     'condition'=>
                                        $join_table . '.' . Inflector::singularize($this->owner) . '_id=' . $this->pk
                                    )
                                )
                            );
        $this->post_execution();
        return $ret;
    }
}
