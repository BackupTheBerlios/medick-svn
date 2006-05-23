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

// ActiveRecord dependencies.
include_once('active/record/DatabaseRow.php');
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
    protected $row;

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
     * Establish A Database Connection
     *
     * @return Creole database connection
     */
    public static function establish_connection () {
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

    /**
     * Constructor
     *
     * @param array, params, parameters as pair of `field name` => `value`
     * @final because there is no reason to overwrite in parent classes, PHP Engine will call this constructor by default.
     */
    public function ActiveRecord($params = array()) {
        ActiveRecord::establish_connection();
        $this->class_name = $this->getClassName();
        $this->table_name = Inflector::pluralize(strtolower(Inflector::underscore($this->class_name)));
        
        $table_info = ActiveRecordTableInfo::getInstance(ActiveRecord::$conn, $this->table_name);
        // $table_info = ActiveRecord::$conn->getDatabaseInfo()->getTable($this->table_name);
        $this->pk   = $table_info->getPrimaryKey()->getName();

        $this->row = new DatabaseRow($this->table_name);
        foreach( $table_info->getColumns() as $col) {
            $field = new Field( $col->getName() );
            // $field->size = $col->getSize();
            $field->type = CreoleTypes::getCreoleName( $col->getType() ) ;
            // set is_nullable
            // $field->isNullable = (bool)$col->isNullable;
            if ($this->pk == $col->getName() ) {
                $field->isPk = TRUE;
            }
            // set the is_fk and fk_table
            $pattern = '/^(.*)_id$/';
            if ( preg_match($pattern, $col->getName(), $matches) ) {
                $field->isFK = true;
                $field->fKTable = $matches[ 1 ];
            } else {
                $field->isFK = false;
            }
            $this->row[]= $field;
        }
        // confused?
        if(!empty($params)) { foreach ($params as $field_name => $field_value) {
            $this->$field_name = $field_value;
        }}
    }

    // {{{ __magic functions
    /**
     * It sets the value of the field
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @param mixed, field_value, field value
     * @throws ActiveRecordException if the field is not found.
     */
    public function __set($field_name, $field_value) {
        if ($field= $this->row->getFieldByName($field_name)) {
            return $this->row->updateStatus($field, $field_value);
        }
        throw new ActiveRecordException (
            'Cannot Set the value of field: `' . $field_name . '`. No such field!');
    }

    /**
     * It gets the value of the field
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @throws ActiveRecordException
     * @return field value
     */
    public function __get($field_name) {
        if ($field= $this->row->getFieldByName($field_name)) {
            return $field->isAffected ? $field->getValue() : NULL;
        }
        try {
            return Association::resolve(
                                array(
                                    'has_one'    => $this->has_one,
                                    'belongs_to' => $this->belongs_to,
                                    'has_many'   => $this->has_many,
                                    'has_and_belongs_to_many' => $this->has_and_belongs_to_many
                                    ),
                                $this->table_name,
                                $field_name,
                                $this->row
                                )->execute();
        } catch (AssociationNotFoundException $anfEx) {
            throw new ActiveRecordException (
                'Cannot Get the value of filed: `' . $field_name . '`. No such filed!',
                $anfEx->getMessage());
        }
    }

    /**
     * @todo This method is not working as expected!
     *
     * This method is run before any call to ActiveRecord public methods! (nope: php 5.1.2)
     * Removes some duplicate code from the list with <tt>know_methods</tt>.
     * ALso, it defines some methods aliases (eg: delete===distroy)
     *
     * Basically it checks before save, insert, update or delete calls that
     * the current run has affected fields and throws an ActiveRecordException if not.
     *
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string method name
     * @param array arguments
     * @throws ActiveRecordException
    public function __call($method, $arguments) {
        if ($method == 'destroy') return $this->delete();
        $know_methods = array('save', 'insert', 'update', 'delete');
        if (!in_array($method, $know_methods)) {
            trigger_error(
                sprintf(
                    'Call to undefined method: %s::%s().', $this->getClassName(), $method), E_USER_ERROR);
        } elseif(!$this->fields->hasAffected()) {
            throw new ActiveRecordException('No field was set before ' . $method);
        } else {
            $this->$method($arguments[0]);
        }
    }
    */    

    /** returns a string representation of this object */
    public function toString() {
        $string = '';
        foreach ($this->row->getAffectedFields() as $field) {
            $string .= "[ " . $field->type . " ] " . $field->getName() . " : " . $field->getValue() . "\n";
        }
        return $string;
    }

    /** Prepare this Object for serialization */
    public function __sleep() {
        return array('row', 'table_name', 'class_name','has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many');
    }

    /** restore the Object state after unserialize  */
    public function __wakeup() {
        ActiveRecord::establish_connection();
        $it= $this->row->iterator();
        while($it->hasNext()) {
            $current= $it->next();
            $this->__set($current->getName(), $current->getValue());
        }
    }

    // }}}

    /**
     * It gets the current database row
     *
     * @return DatabaseRow
     */
    public function getRow() {
        return $this->row;
    }

    /**
     * Check if this row is valid by counting the associated rows errors
     *
     * @return true if is valid
     */ 
    public function isValid() {
        return count($this->row->collectErrors()) == 0;
    }

    /**
     * Validates this row
     *
     * @return Validator
     */ 
    protected function validates () {
        return new Validator($this->row);
    }

    // {{{ filters
    /**
     * Before Insert Filter.
     *
     * This filter is executed before running an sql insert.
     * You should overwrite this method in your models.
     * Remember to return TRUE and check with === FALSE to get the error
     * 
     * @return bool
     * @since Rev.272
     */
    protected function before_insert() { return TRUE; }

    /**
     * Before Update Filter.
     *
     * This filter is executed before running an sql update.
     * You should overwrite this method in your models.
     * Remember to return TRUE and check with === FALSE to get the error
     * 
     * @return bool
     * @since Rev.272
     */
    protected function before_update() { return TRUE; }

    /**
     * Before Delete Filter.
     *
     * This filter is executed before running an sql delete.
     * You should overwrite this method in your models.
     * Remember to return TRUE and check with === FALSE to get the error
     * 
     * @return bool
     * @since Rev.272
     */
    protected function before_delete() { return TRUE; }

    /**
     * Before Save Filter.
     *
     * This filter is executed before running an sql insert or update
     * You should overwrite this method in your models.
     * Remember to return TRUE and check with === FALSE to get the error
     * 
     * @return bool
     * @since Rev.342
     */
    protected function before_save() { return TRUE; }

    /**
     * After Insert Filter.
     *
     * This filter is executed after running the sql insert.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function after_insert()  {    }

    /**
     * After Update Filter.
     *
     * This filter is executed after running the sql update.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function after_update()  {    }

    /**
     * After Delete Filter.
     *
     * This filter is executed after running the sql delete.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function after_delete()  {    }

    // }}}

    // {{{ save
    /**
     * Save,
     *    will do a SQL Insert and return the last_inserted_id or
     * an Update returning the number of affected rows.
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
        if ($this->row->getPrimaryKey()->isAffected) {
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
        if ( !$this->before_insert() or count($this->row->collectErrors()) > 0) {
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
        if ( !$this->before_update() or count($this->row->collectErrors()) > 0) {
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
        if (!$this->before_delete() || count($this->row->collectErrors()) > 0) {
            return false;
        }
        if ($this->row->getPrimaryKey() !== NULL && $this->row->getPrimaryKey()->getValue()===NULL) {
            throw new ActiveRecordException('Refusing to delete everything from ' . $this->table_name . ', Primary Key was NULL');
        }
        $sql= 'delete from ' . $this->table_name . ' where id=?';
        $stmt= ActiveRecord::$conn->prepareStatement($sql);
        $stmt->setInt(1, $this->row->getPrimaryKey()->getValue());
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
        if ($this->row->getPrimaryKey() !== NULL) {
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
        ActiveRecord::populateStmtValues($stmt, $this->row->getAffectedFields());
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
            $sqlSnippet = ' WHERE ' . $this->pk . ' = ' . $this->row->getPrimaryKey()->getValue();
        }
        $sql  = 'UPDATE ' . $this->table_name . ' SET ';
        // $sql .= implode(' = ?, ', $this->row->getAffectedFieldsNames());

        foreach($this->row->getAffectedFields() as $field) {
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
               . ' (' . implode(',', $this->row->getAffectedFieldsNames()) . ')'
               . ' VALUES (' . substr(str_repeat('?,', count($this->row->getAffectedFields())), 0, -1) . ')';
    }

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
    // }}}

    /**
     * This method should be overwritten in child classes, 
     * from php 5.2 you cannot declare a method as abstract and static
     */
    public static function find() {
        throw new MedickException('ActiveRecord::find() should be overwritten in child classes!');
    }

    public static function build(QueryBuilder $builder) {
        $class_name= $builder->getOwner();
        try {
            // prepare the class instance.
            $class = new ReflectionClass($class_name);
        } catch (ReflectionException $rEx) {
            Registry::get('__injector')->inject('model', strtolower($class_name));
            // retry:
            $class = new ReflectionClass($class_name);
        }
        ActiveRecord::establish_connection();
        $stmt = ActiveRecord::$conn->prepareStatement($builder->compile()->getQueryString());
        $i=1; foreach($builder->getBindings() as $binding) {
            $stmt->set($i++, $binding);
        }
        if ($limit  = $builder->getLimit())  $stmt->setLimit($limit);
        if ($offset = $builder->getOffset()) $stmt->setOffset($offset);
        $rs = $stmt->executeQuery();
        Registry::get('__logger')->debug('Query: ' . ActiveRecord::$conn->lastQuery);
        if ($builder->getType() == 'first') {
            if ($rs->getRecordCount() == 1) {
                $rs->next();
                $result= $class->newInstance($rs->getRow());
                $stmt->close();$rs->close();
                return $result;
            } else {
                throw new RecordNotFoundException(
                    'Couldn\'t find a `' . $class_name . '` to match your query.');
            }
        }
        $stmt->close();
        return ActiveRecord::merge($rs, $class);
    } 
    
    /**
     * @return RowsAggregate
     */ 
    protected static function merge(ResultSet $rs, ReflectionClass $class) {
        $results= new RowsAggregate();
        while($rs->next()) {
            $results->add($class->newInstance($rs->getRow()));
        }
        $rs->close();
        return $results;
    }
    
}

