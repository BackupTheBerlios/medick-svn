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
 *
 * As it is right now, associations are read-only
 * this means that you cannot save the data using an association.
 * This will be fixed probably in a next medick version.
 * 
 * @package medick.active.record
 * @subpackage association
 * @author Oancea Aurelian
 */
abstract class Association extends Object {

    /** @var string 
        current object name */
    protected $owner= NULL;

    /** @var string
        returning object name(to search for) */
    protected $class= NULL;

    /** @var int
        value of the primary key */
    protected $pk   = NULL;

    /** @var DatabaseRow 
        */
    protected $fields;

    /**
     * It sets this Association Fields
     *
     * @param DatabaseRow fields
     */ 
    public function setFields($fields) {
        $this->fields = $fields;
    }
    
    /**
     * It sets the current class to search for
     *
     * @param string class, class name
     */ 
    public function setClass($class) {
        $this->class = $class;
    }

    /**
     * It sets the current owner
     *
     * @param string owner
     */ 
    public function setOwner($owner) {
        $this->owner = $owner;
    }

    /**
     * It sets the primary key value
     *
     * @param int pk primary key value
     */ 
    public function setPk($pk) {
        $this->pk = $pk;
    }

    /** 
     * Executes the current solved association
     *
     * @throws AssociationNotFoundException
     */
    abstract public function execute();
    
    /**
     * Resolves this Association
     *
     * Returns a new instance of the solved Association and it acts as a factory
     * @param array associations ActiveRecord defined associations
     * @param string owner current ActiveRecord woner name
     * @param string class name of the object we want to return
     * @param DatabaseRow fields ActiveRecord fields
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
 *     class Author extends ActiveRecord {
 *         protected $has_many=array('articles'); // plural!
 *         // same as:
 *         // protected $has_many='articles';
 * </code>
 *
 * @package medick.active.record
 * @subpackage association
 * @author Oancea Aurelian
 * @since Rev. 272
 */
class HasManyAssociation extends Association {

    /** @see Association::execute() */
    public function execute() {
        $fk= Inflector::singularize($this->owner) . '_id';
        $arguments= array('all', array('condition'=>$fk.'=?'), array($this->pk));
        $builder= new QueryBuilder(Inflector::singularize($this->class), $arguments);
        return ActiveRecord::build($builder);
    }

}

/**
 * HasOne Association type
 *
 * It solves associations based on sql foreign keys
 * <code>
 *     class Article extends ActiveRecord {
 *         protected $has_one=array('author');
 *         // same as:
 *         // protected $has_one='author';
 * </code>
 *
 * @package medick.active.record
 * @subpackage association
 * @author Oancea Aurelian
 * @since Rev. 272
 */
class HasOneAssociation extends Association {

    /**
     * It Executes this Association
     * 
     * @todo what if we don`t find the field?
     * @see Association::execute()
     */
    public function execute() {
        $fk= $this->class.'_id'; // foreign key name: the class name+"_id" suffix"
        if ($field= $this->fields->getFieldByName($fk)) {
            $arguments= array('first', array('condition'=>'id=?'), array($field->getValue()));
            return ActiveRecord::build(new QueryBuilder($this->class, $arguments));
        } else {
            throw new AssociationNotFoundException('Cannot execute Association ``has_one" on ' . $this->class);
        }
    }
}

/**
 * BelongsToAssociation
 *
 * A Special case of HasOneAssociation
 * <code>
 *     class Article extends ActiveRecord {
 *         protected $belongs_to=array('author');
 *         // same as:
 *         // protected $belongs_to='author';
 * </code>
 *
 * @package medick.active.record
 * @subpackage association
 * @author Oancea Aurelian
 * @since Rev. 272
 */
class BelongsToAssociation extends HasOneAssociation {    }

/**
 * HasAndBelongsToManyAssociation
 * 
 * @package medick.active.record
 * @subpackage association
 * @author Oancea Aurelian
 */
class HasAndBelongsToManyAssociation extends Association {

    /** @see Association::execute() */
    public function execute() {
        if ($this->class < $this->owner) {
            $join_table= $this->class . '_' . Inflector::pluralize($this->owner);
        } else {
            $join_table= Inflector::pluralize($this->owner) . '_' . Inflector::pluralize($this->class);
        }
        $arguments=array();
        $arguments[]='all';
        $clauses= array();
        $clauses['columns']   = $this->class.'.*';
        $clauses['left join'] = $join_table . ' on ' . $this->class . '.id=' . $join_table . '.' . Inflector::singularize($this->class) . '_id';
        $clauses['condition'] = $join_table . '.' . Inflector::singularize($this->owner) . '_id=?';
        $arguments[]= $clauses;
        $arguments[]= array($this->pk);
        return ActiveRecord::build(new QueryBuilder(Inflector::singularize($this->class), $arguments));
    }
}

