<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian[at]locknet[dot]ro>
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

// ActiveRecord dependencies.
// include_once('active/record/DatabaseRow.php');
include_once('active/record/Field.php');
include_once('active/record/RowsAggregate.php');
include_once('active/record/QueryBuilder.php');
include_once('active/record/SQLCommand.php');
include_once('active/record/Association.php');
include_once('active/record/Validator.php');
include_once('active/support/Inflector.php');
// 3-rd party.
include_once('creole/Creole.php');
include_once('creole/CreoleTypes.php');

class ActiveRecordTableInfo extends Object {
    static $instance= NULL;
    static function getInstance(Connection $conn, $table_name) {
        if (self::$instance === NULL || !isset(self::$instance[$table_name])) {
            self::$instance[$table_name]= $conn->getDatabaseInfo()->getTable($table_name);
        }
        return self::$instance[$table_name];
    }
}

/**
 * Main ActiveRecord Class
 *
 * @package medick.active.record
 * @author Oancea Aurelian
 */
abstract class ActiveRecord extends Object {

    /** @var string
        class name: Person */
    protected $class_name = NULL;

    /** @var string
        table mane: persons */
    protected $table_name = NULL;

    /** @var CreoleConnection
        database connection */
    static protected $conn= NULL;

    /** @var DatabaseRow
        our database row. */
    // protected $row;

    protected $fields=array();
    
    private $validators= array();
    
    private $errors= array();

    private $collected= FALSE;
    
    /** @var string
        primary key name */
    private $pk;

    // {{{ Associations
    protected $has_one= array();
    protected $has_many= array();
    protected $belongs_to= array();
    protected $has_and_belongs_to_many= array();
    // }}}

    /**
     * Constructor
     *
     * @param array, params, parameters as pair of `field name` => `value`
     * @final because there is no reason to overwrite in parent classes, PHP Engine will call this constructor by default.
     */
    public function ActiveRecord($params= array()) {
        $this->class_name = $this->getClassName();
        $this->table_name = Inflector::pluralize(strtolower(Inflector::underscore($this->class_name)));
        $table_info = ActiveRecordTableInfo::getInstance(ActiveRecord::connection(), $this->table_name);
        $this->pk   = $table_info->getPrimaryKey()->getName();
        foreach( $table_info->getColumns() as $col ) {
            $field = new Field( $col->getName() );
            // $field->size = $col->getSize();
            $field->type = CreoleTypes::getCreoleName( $col->getType() ) ;
            // set is_nullable
            // $field->isNullable = (bool)$col->isNullable;
            if ($this->pk == $col->getName() ) {
                $field->isPk = true;
            }
            // set the is_fk and fk_table
            $pattern = '/^(.*)_id$/';
            if ( preg_match($pattern, $col->getName(), $matches) ) {
                $field->isFK = true;
                $field->fKTable = $matches[ 1 ];
            } else {
                $field->isFK = false;
            }
            $this->fields[$field->getName()]= $field;
        }
        // confused?
        if(!empty($params)) { foreach ($params as $field_name => $field_value) {
            $this->$field_name = $field_value;
        }}
    }

    /**
     * It sets the value of the field
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @param mixed, field_value, field value
     * @throws ActiveRecordException if the field is not found.
     */
    public function __set($name, $value) {
        if ($this->hasField($name)) $this->getField($name)->setValue($value);
        else throw new ActiveRecordException('No such Filed: ' . $name);
    }

    /**
     * It gets the value of the field
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @throws ActiveRecordException
     * @return field value
     */
    public function __get($name) {
        if ($this->hasField($name)) return $this->getField($name)->getValue();
        try {
            return Association::resolve($this, $name)->execute();
        } catch (AssociationNotFoundException $anfEx) {
            throw new ActiveRecordException ('Cannot Get the value of filed: `' . $name . '`. No such filed!', $anfEx->getMessage());
        }
    }
    
    /**
     * Implements magick medick methods
     * 
     * <b>Available methods:</b><br />
     * <ul>
     *  <li>
     *    <i>validates_</i>it loads a Validator, eg. validates_presence_of will load PresenceOfValidator<br />
     *    @see Validator
     *  </li>
     *  <li>
     *    <i>before_</i>, if not defined, a call to a before filter will return true
     *  </li>
     *  <li>
     *    <i>after_</i>, if not defined this will return
     *  </li>
     *  <li>
     *    <i>get</i>, if not defined will try to return a Field, eg.: assuming Person is an ActiveRecord class:<br />
     *    <code>
     *      $p= Person::find(1);
     *      $p->getName(); // returns a Field object
     *      $p->name; // returns the Field value
     *      $p->getName()->getValue(); // the same as the above call
     *      $p->hasField('name') && $p->getField('name')->getValue(); // the same
     *    </code>
     *  </li>
     *  <li>
     *    <i>set</i>, if not defined will try to set the value of a Field, eg.: assuming Person is an ActiveRecord class:<br />
     *    <code>
     *      $p= new Person();
     *      $p->name= 'Andy'; // sets the person name to Andy
     *      $p->setName('Andy'); // same as above
     *    </code>
     *  </li>
     * </ul>
     * 
     * @TODO: more checks on before_ / after_ filters
     *
     * @throws MedickException if the called method is not defined (similar with php error)
     */ 
    public function __call($method, $args) {
        if (substr($method,0,10)== 'validates_') { 
            $cname= str_replace(" ", "", ucwords(str_replace("_", " ", substr($method, 10)))) . "Validator";
            $validator= new $cname;
            $validator->fields($args);
            $validator->record($this);
            $this->validators[]= $validator;
            return $validator;
        }
        if (substr($method,0,7) == 'before_') return true; 
        if (substr($method,0,6) == 'after_')  return;
        if (substr($method,0,3) == 'get' && $this->hasField(strtolower(substr($method, 3)))) {
            return $this->getField(strtolower(substr($method, 3)));
        }
        if (substr($method,0,3) == 'set' && $this->hasField(strtolower(substr($method, 3)))) {
            return $this->getField(strtolower(substr($method,3)))->setValue($args[0]);
        }
        throw new MedickException('Call to a undefined method: ' . $this->getClassName() . '::' . $method);
    }
       
    /** 
     * Returns a string representation of this Object 
     *
     * @return string
     */
    public function toString() {
        $string = ''; foreach ($this->getAffectedFields() as $field) {
            $string .= "[ " . $field->type . " ] " . $field->getName() . " : " . $field->getValue() . "\n";
        } return $string;
    }

    /** 
     * Prepare this Object for serialization
     *
     * @return Array
     */
    public function __sleep() {
        return array('fields', 'table_name', 'class_name','has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many');
    }

    /** 
     * Restore the Object state after unserialize
     *
     * @return void
     */
    public function __wakeup() {
        ActiveRecord::connection();
        foreach ($this->fields as $field) {
            $this->__set($field->getName(), $field->getValue());
        }
    }
    
    /**
     * It gets the database table name
     *
     * @return string
     */ 
    public function getTableName() {
        return $this->table_name;
    }
    
    /**
     * Check if it has a Filed with the given name
     *
     * @param string Filed name
     * @return bool
     */ 
    public function hasField($name) {
        return in_array($name, array_keys($this->fields));
    }
    
    /**
     * It gets all the Fields of this Object
     *
     * @return Array
     */ 
    public function getFields() {
        return $this->fields;
    }
    
    /**
     * It gets a Field by it's name
     *
     * @param string Field name
     * @return Field
     */ 
    public function getField($name) {
        return $this->fields[$name];
    }
    
    /**
     * It gets the Filed that is Primary Key on this table
     *
     * @return Field
     */ 
    public function getPrimaryKey() {
        foreach($this->fields as $field) {
            if ($field->isPk) return $field;
        }
    }
    
    /**
     * It gets the associations of this Object
     * 
     * @return Array
     */ 
    public function getAssociations() {
        return array('has_one'    => $this->has_one,
                     'belongs_to' => $this->belongs_to,
                     'has_many'   => $this->has_many,
                     'has_and_belongs_to_many' => $this->has_and_belongs_to_many
               );
    }
    
    public function hasErrors() {
        return sizeof($this->errors) > 0;
    }

    public function getErrors() {
        return $this->errors;
    }
    
    public function isValid($force= FALSE) {
        // return $this->collect_errors() === 0;
        if ($this->collected) return !$this->hasErrors();
        else return $this->collect_errors($force) === 0;
    }

    private function collect_errors($force= FALSE) {
        if ($this->collected && !$force) return sizeof($this->errors);
        $this->run_validators();
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) {
                foreach($field->getErrors() as $error) {
                    $this->errors[] = $error;
                }
                // $this->errors= $field->getErrors();
            }
        }
        $this->collected= TRUE;
        return sizeof($this->errors);
    }
    
    private function run_validators() {
        foreach ($this->validators as $v) {
            $v->validate_each();
        }
    }
    
    // {{{ save
    /**
     * Save,
     *    will do a SQL Insert and return the last_inserted_id 
     *    or an Update returning the number of affected rows.
     * If the primary key is affected (changed) on this run we will do an update, otherwise an insert.
     * <code>
     *      $author = new Author();
     *      $author->name = 'Mihai';
     *      $author->firstName = 'Eminescu';
     *      $author->save(); // will do the insert, returning the ID of the last field inserted.
     *      // a mistake, let`s update.
     *      $author->firstName = 'Sadoveanu';
     *      $author->save(); // performs the update and returns the number of affected rows (1).
     * </code>
     */
    public function save() {
        if ( !$this->before_save() || !$this->isValid()) {
            return false;
        }
        if ($this->getPrimaryKey()->isAffected) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    // }}}

    // {{{ insert
    /**
     * Executes an SQL insert
     *
     * <code>
     *     $author = new Author();
     *     $author->name= 'Mihai';
     *     $author->insert();
     *     // is translated into:
     *     // INSERT INTO authors (name) VALUES ('Mihai');
     * </code>
     *
     * @return int next primary key id or, 1 (affected rows).
     * @throws SQLException
     */
    public function insert() {
        if ( !$this->before_insert() || !$this->isValid()) {
            return false;
        }
        $af_rows = $this->performQuery($this->getInsertSql());
        $id = $this->getNextId();
        $this->after_insert();
        return $id ? $id : $af_rows;
    }
    // }}}

    // {{{ update
    /**
     * Executes a SQL update
     *
     * <code>
     *     $author = new Author(array('id'=>5));
     *     // or: $author= new Author(); $author->id = 5;
     *     $author->name= 'Mihai';
     *     $author->update();
     *     // is translated into:
     *     // UPDATE authors set name='Mihai' WHERE id=5;
     * </code>
     *
     * @return int affected rows.
     * @throws SQLException
     */
    public function update() {
        if ( !$this->before_update() || !$this->isValid()) {
            return false;
        }
        $af= $this->performQuery($this->getUpdateSql());
        $this->after_update();
        return $af;
    }
    
    /**
     * Sets an array af attributes
     *
     * <code>
     *   $author= Author::find(5); // select * from authors where id=5;
     *   $author->attributes(array('name'=>'Jon'))->save(); // update authors set name='Jon' where id=5;
     * </code>
     * This method is also useful when receiving an array of parameters from HTTPRequest (form).
     * <code>
     *   // controller
     *   $user= User::find($request->getParameter('id'))->attributes($request->getParameter('user'))->save();
     * </code>
     *
     * @return ActiveRecord
     */ 
    public function attributes(/*Array*/ $params=array()) {
        foreach($params as $name=>$value) {
            $this->$name=$value;
        }
        return $this;
    }

    // }}}

    // {{{ delete
    /**
     * Performs an SQL delete.
     *
     * <code>
     *     $affected_rows= new Author(array('id'=>5, 'name'=>'Mihai'))->delete();
     *     // translated into:
     *     // DELETE FROM authors WHERE id=5 and name='Mihai';
     *     $affected_rows = new Author(array('name'=>'Mihai'))->delete();
     *     // is translated to:
     *     // DELETE FROM authors WHERE name='Mihai'
     * </code>
     *
     * @return int affected rows.
     * @throws SQLException
     */
    public function delete() {
        if (!$this->before_delete() || !$this->isValid()) {
            return false;
        }
        if ($this->getPrimaryKey() !== NULL && $this->getPrimaryKey()->getValue()===NULL) {
            throw new ActiveRecordException('Refusing to delete everything from ' . $this->table_name . ', Primary Key was NULL');
        }
        $sql= 'delete from ' . $this->table_name . ' where id=?';
        $stmt= ActiveRecord::$conn->prepareStatement($sql);
        $stmt->setInt(1, $this->getPrimaryKey()->getValue());
        $af_rows= $stmt->executeUpdate();
        $this->after_delete();
        return $af_rows;
    }
    // }}}

    // {{{ private methods (internal helpers)
    /**
     * It gets the next primary key id
     *
     * @return string the next id, or false when the table dont have a primary key
     */
    private function getNextId() {
        if ($this->getPrimaryKey() !== NULL) {
            $_pk = $this->pk;
            $id  = ActiveRecord::$conn->getIdGenerator()->getId($this->pk);
            $this->$_pk = $id;
            return $id;
        } else {
            return false;
        }
    }

    /**
     * @todo can we use QueryBuilder for this?
     * Helper, internal method witch performs an sql query, other than select.
     *
     * @param string sql the sql query to execute
     * @return int affected rows
     * @throws SQLException
     */
    private function performQuery($sql) {
        $stmt = ActiveRecord::$conn->prepareStatement($sql);
        ActiveRecord::populateStmtValues($stmt, $this->getAffectedFields());
        $af_rows = $stmt->executeUpdate();
        $stmt->close();
        Registry::get('__logger')->debug('Query: ' . ActiveRecord::$conn->lastQuery);
        // $this->_reset();
        return $af_rows;
    }

    /**
     * It gets the sql snippet that will be  used to execute an update
     *
     * FIXME:
     * <tt>UPDATE __TABLE__ SET foo='12' WHERE bar='ee';</tt>
     * is not working.
     *
     * @return string
     */
    private function getUpdateSql() {
        $sqlSnippet = '';
        if ($this->pk !== NULL) {
            $sqlSnippet = ' WHERE ' . $this->pk . ' = ' . $this->getPrimaryKey()->getValue();
        }
        $sql  = 'UPDATE ' . $this->table_name . ' SET ';
        // $sql .= implode(' = ?, ', $this->row->getAffectedFieldsNames());

        foreach($this->getAffectedFields() as $field) {
            $sql .= $field->getName() . ' = ?, ';
        }
        return substr($sql, 0, -2) . $sqlSnippet;
    }

    /**
     * It gets the sql snippet to use for an insert
     * @return string
     */
    private function getInsertSql() {
        return 'INSERT INTO ' . $this->table_name
               . ' (' . implode(',', $this->getAffectedFieldsNames()) . ')'
               . ' VALUES (' . substr(str_repeat('?,', count($this->getAffectedFields())), 0, -1) . ')';
    }
    // }}}
    
    private function getAffectedFields() {
        $fields= array();
        foreach ($this->fields as $field) {
            if ($field->isAffected) $fields[]= $field;
        }
        return $fields;
    }
    
    private function getAffectedFieldsNames() {
        $fields= array();
        foreach ($this->fields as $field) {
            if ($field->isAffected) $fields[]= $field->getName();
        }
        return $fields;
    }
    
    // {{{ Static ActiveRecord
    /**
     * populates stmt values (?,?,?) on sql querys
     * @param PreparedStatement, stmt, the prepared statement.
     * @param array, fields, the affected fields
     */
    private static function populateStmtValues($stmt, $fields) {
        $i = 1;
        foreach($fields as $field) {
            if($field->getValue() === NULL){
                $stmt->setNull($i++);
            } else {
                $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::getCreoleCode(strtoupper($field->type)));
                $stmt->$setter($i++, $field->getValue());
            }
        }
    }
    
    /**
     * Executes a plain sql query
     *
     * @param string sql query to execute
     * @return ResultSet
     */ 
    protected static function execute($sql) {
        $r= ActiveRecord::connection()->executeQuery($sql);
        Registry::get('__logger')->debug(ActiveRecord::$conn->lastQuery);
        return $r;
    }

    /**
     * This method should be overwritten in child classes, 
     * from php 5.2 you cannot declare a method as abstract and static, or can you?
     *
     * @see ActiveRecord::build
     */
    public static function find() {
        throw new MedickException('ActiveRecord::find() should be overwritten in child classes!');
    }
       
    /**
     * @return ActiveRecord or a RowsAggregate (Collection of ActiveRecords)
     */
    public static function build(QueryBuilder $builder) {
        $class= ActiveRecord::reflect_class($builder->getOwner());
        $rs= ActiveRecord::create_result_set($builder);
        if ($builder->getType() == 'first') return ActiveRecord::fetch_one($rs, $class);
        return ActiveRecord::fetch_all($rs, $class);
    } 
 
    /**
     * It knows how to load a model class and how to reflect this class
     *
     * @return ReflectionClass
     */
    protected static function reflect_class($class_name, $r=0) {
        try {
            return new ReflectionClass($class_name);
        } catch (ReflectionException $rEx) {
            Registry::get('__injector')->inject('model', strtolower($class_name));
            if($r==0) return ActiveRecord::reflect_class($class_name, 1);
        }
    }
    
    /**
     * Creates a ResultSet from a QueryBuilder
     *
     * @return ResultSet
     */
    protected static function create_result_set(QueryBuilder $builder) {
        $stmt = ActiveRecord::connection()->prepareStatement($builder->compile()->getQueryString());
        $i=1; foreach($builder->getBindings() as $binding) $stmt->set($i++, $binding);
        if ($limit  = $builder->getLimit())  $stmt->setLimit($limit);
        if ($offset = $builder->getOffset()) $stmt->setOffset($offset);
        $rs= $stmt->executeQuery();
        Registry::get('__logger')->debug('Query: ' . ActiveRecord::$conn->lastQuery);
        $stmt->close();
        return $rs;
    }
 
    /**
     * Returns an ActiveRecord object
     *
     * @throws RecordNotFoundException 
     * @return ActiveRecord
     */
    protected static function fetch_one(ResultSet $rs, ReflectionClass $class) {
        if($rs->getRecordCount() != 1) {
            $rs->close();
            throw new RecordNotFoundException('Couldn\'t find a `' . $class->getName() . '` to match your query.');
        }
        $rs->next();
        $ar= $class->newInstance($rs->getRow());
        $rs->close();
        return $ar;

    }

    /**
     * Merge ResultSet into RowsAggregate
     * @return RowsAggregate
     */ 
    protected static function fetch_all(ResultSet $rs, ReflectionClass $class) {
        $results= new RowsAggregate();
        while($rs->next()) {
            $results->add($class->newInstance($rs->getRow()));
        }
        $rs->close();
        return $results;
    }

    /**
     * Establish A Database Connection
     *
     * @return Creole database connection
     * @deprecate use ActiveRecord::connection, I want to use short names
     */
    public static function establish_connection() {
        return ActiveRecord::connection();
    }

    protected static function connection() {
        if (ActiveRecord::$conn === NULL) {
            ActiveRecord::$conn = Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn());
        }
        return ActiveRecord::$conn;
    }

    /**
     * Close the Database Connection
     */
    public static function close_connection() {
        ActiveRecord::$conn= Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn())->close();
    }
    // }}}
}

