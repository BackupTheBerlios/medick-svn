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
// include_once('active/record/FieldsAggregate.php');
include_once('active/record/DatabaseRow.php');
include_once('active/record/RowsAggregate.php');
include_once('active/record/QueryBuilder.php');
include_once('active/support/Inflector.php');
include_once('active/record/Association.php');
include_once('active/record/Validator.php');
// 3-rd party.
include_once('creole/Creole.php');

/**
 * @package locknet7.active.record
 */
abstract class ActiveRecordBase extends Object {

    /* class name: Person */
    static protected $class_name = NULL;
    /* table mane: persons */
    static protected $table_name = NULL;
    /* database connection */
    static protected $conn       = NULL;

    /** @var FieldsAggregate
        DB Table Fields */
    // protected $fields;

    protected $row;

    /** @var string
        primary key name! */
    private $pk;

    // {{{ Associations
    protected $has_one= array();
    protected $has_many= array();
    protected $belongs_to= array();
    protected $has_and_belongs_to_many= array();
    // }}}

    /**
     * Establish A Database Connection
     */
    public static final function establish_connection () {
        if (self::$conn === NULL) {
            self::$conn = Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn());
        }
    }

    /**
     * Close the Database Connection
     */
    public static final function close_connection() {
        self::$conn = Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn())->close();
    }

    /**
     * Constructor
     *
     * Is final, because there is no reason to overwrite in parent classes.
     * PHP Engine will call this constructor by default.
     * @param array, params, parameters as pair of `field name` => `value`
     */
    public final function __construct($params = array()) {
        self::establish_connection();
        self::$class_name = $this->getClassName();
        self::$table_name = Inflector::pluralize(strtolower(Inflector::underscore(self::$class_name)));

        $table_info   = self::$conn->getDatabaseInfo()->getTable(self::$table_name);
        $this->pk     = $table_info->getPrimaryKey()->getName();

        $this->row = new DatabaseRow();
        foreach( $table_info->getColumns() as $col) {
            $field = new Field( $col->getName() );
            // $field->size = $col->getSize();
            $field->type = CreoleTypes::getCreoleName( $col->getType() ) ;
            $field->formattedName =  str_replace( '_', ' ', $col->getName() );
            // set is_nullable
            // $field->isNullable = (bool)$col->isNullable;
            if ($this->pk == $col->getName() ) $field->isPk = TRUE;
            // set the is_fk and fk_table
            $pattern = '/^(.*)_id$/';
            if ( preg_match($pattern, $col->getName(), $matches) ) {
                $field->isFK = true;
                $field->fKTable = $matches[ 1 ];
            } else {
                $field->isFK = false;
            }
            $this->row[] = $field;
        }

        foreach ($params as $field_name => $field_value) {
            $this->$field_name = $field_value;
        }
    }

    // {{{ __magic functions
    /**
     * It sets the value of the field
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @param mixed, field_value, field value
     * @throw ActiveRecordException if the field is not found.
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
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @throw ActiveRecordException
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
                                self::$table_name,
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
     * This method is run before any call to ActiveRecordBase public methods!
     * Removes some duplicate code from the list with <tt>know_methods</tt>.
     * ALso, it defines some methods aliases (eg: delete===distroy)
     *
     * Basically it checks before save, insert, update or delete calls that
     * the current run has affected fields and throws an ActiveRecordException if not.
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string method name
     * @param array arguments
     * @throws ActiveRecordException
     */
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

    /** returns a string representation of this object */
    public function __toString() {
        $string = '';
        foreach ($this->fields->getAffectedFields() as $field) {
            $string .= "[ " . $field->type . " ] " . $field->getName() . " : " . $field->getValue() . "\n";
        }
        return $string;
    }

    /** Prepare this Object for serialization */
    public function __sleep() {
        self::close_connection();
        return array('fields', 'pk', 'has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many');
    }

    /** restore the Object state after unserialize */
    public function __wakeup() {
        self::establish_connection();
        for($it = $this->fields->getIterator(); $it->valid(); $it->next()) {
            $field_name= $it->current()->getName();
            $this->$field_name = $it->current()->getValue();
        }
    }

    // }}}

    public final function getRow() {
        return $this->row;
    }

    public final function validates () {
        return new Validator($this->row);
    }

    // {{{ filters:
    /**
     * Before Insert Filter.
     *
     * This filter is executed before running an sql insert.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function before_insert() {    }

    /**
     * Before Update Filter.
     *
     * This filter is executed before running an sql update.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function before_update() {    }

    /**
     * Before Delete Filter.
     *
     * This filter is executed before running an sql delete.
     * You should overwrite this method in your models.
     * @return void
     * @since Rev.272
     */
    protected function before_delete() {    }

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
    public final function save() {
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
    public final function insert() {
        $this->before_insert();
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
    public final function update() {
        $this->before_update();
        $af= $this->performQuery($this->getUpdateSql());
        $this->after_update();
        return $af;
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
    public final function delete() {
        $this->before_delete();
        $whereClause = array();
        foreach ($this->row->getAffectedFields() as $col) {
            $whereClause[] = $col->getName() . ' = ? ';
        }
        $sql = 'DELETE FROM ' . ActiveRecordBase::$table_name . ' WHERE ' . implode(' AND ', $whereClause);
        $af= $this->performQuery($sql);
        $this->after_delete();
        return $af;
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
            $id  = ActiveRecordBase::$conn->getIdGenerator()->getId($this->pk);
            $this->$_pk = $id;
            return $id;
        } else {
            return FALSE;
        }
    }

    /**
     * Helper internal method witch performs an sql query
     *
     * @param string sql the sql query to execute
     * @return int affected rows
     * @throws SQLException
     */
    private function performQuery($sql) {
        $stmt = ActiveRecordBase::$conn->prepareStatement($sql);
        ActiveRecordBase::populateStmtValues($stmt, $this->row->getAffectedFields());
        $af_rows = $stmt->executeUpdate();
        $stmt->close();
        Registry::get('__logger')->debug('Performing sql query: ' . ActiveRecordBase::$conn->lastQuery);
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
        $sql  = 'UPDATE ' . ActiveRecordBase::$table_name . ' SET ';
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
        return 'INSERT INTO ' . self::$table_name
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

    abstract static function find();

    // {{{ find monster
    /**
     *
     * @throws ActiveRecordException if a requested case is not yet implemented (or invalid)
     * @throws RecordNotFoundException no record responded to this method
     */
    public static final function __find($params= array()) {
        $numargs = count($params);
        if($numargs == 0) return ActiveRecordBase::__find(array('all'));

        ActiveRecordBase::establish_connection();

        try {
            // prepare the class instance.
            $class = new ReflectionClass(ActiveRecordBase::$class_name);
        } catch (ReflectionException $rEx) {
            Registry::get('__injector')->inject('model', strtolower(ActiveRecordBase::$class_name));
            // retry:
            $class = new ReflectionClass(ActiveRecordBase::$class_name);
        }
        $query = new QuerryBuilder(ActiveRecordBase::$table_name);

        if ( $params[0] == 'all' && $numargs == 1 ) {
            // all table fields and one arg.
        } elseif ( $params[0] == 'all' && $numargs == 2 && is_array($params[1]) && !empty($params[1]) ) {
            $query->addArray($params[1]);
        } elseif ( is_numeric($params[0])) {
            // we expect only one row!
            // we need the pk name.
            $pk_name = self::$conn->getDatabaseInfo()->getTable(self::$table_name)->getPrimaryKey()->getName();
            if ( $numargs == 1 ) {
                $query->add('condition', $pk_name . '=?');
            } elseif ( $numargs == 2 && is_array($params[1]) && !empty($params[1]) ) {
                $query->add('condition', $pk_name . '=?');
                $query->addArray($params[1]);
            }
            $stmt = ActiveRecordBase::$conn->prepareStatement($query->buildQuery());
            $stmt->setInt(1, $params[0]);
            $rs   = $stmt->executeQuery();
            if ($rs->getRecordCount() == 1) {
                $rs->next();
                $stmt->close();
                return $class->newInstance($rs->getRow());
            } elseif ($rs->getRecordCount() == 0) {
                throw new RecordNotFoundException(
                    'Couldn\'t find a `' . ActiveRecordBase::$class_name . '` with ID=' . $params[0]);
            }
        } elseif(is_array($params[0])) {
            $query->addArray($params[0]);
        } else {
            throw new ActiveRecordException('Case Not Implemented yet!');
        }

        $stmt = ActiveRecordBase::$conn->prepareStatement($query->buildQuery());
        // add limit and/or offset if requested
        if ($limit = $query->getLimit())   $stmt->setLimit($limit);
        if ($offset = $query->getOffset()) $stmt->setOffset($offset);
        $rs = $stmt->executeQuery();
        if ($rs->getRecordCount() == 0) {
            throw new RecordNotFoundException(
                'Couldn\'t find a ' . ActiveRecordBase::$class_name . ' The Result Set was empty!');
        }
        // build a list with objects of this type.
        $results = new RowsAggregate();
        while ($rs->next()) {
            $results->add($class->newInstance($rs->getRow()));
        }
        // release resources.
        $rs->close(); $stmt->close();
        Registry::get('__logger')->debug('Performed sql query: ' . ActiveRecordBase::$conn->lastQuery);
        return $results;
    }
    // }}}

    protected static final function initialize($table) {
        ActiveRecordBase::$table_name= strtolower(Inflector::pluralize($table));
        ActiveRecordBase::$class_name= ucfirst($table);
    }
}
