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

// ActiveRecord dependencies.
include_once('active/record/FieldsAggregate.php');
include_once('active/record/RowsAggregate.php');
include_once('active/record/QueryBuilder.php');
include_once('active/support/Inflector.php');
include_once('active/record/Association.php');
// 3-rd party.
include_once('creole/Creole.php');

/**
 * @package locknet7.active.record
 */
class ActiveRecordBase extends Object {

    /** @var FieldsAggregate
        DB Table Fields */
    protected $fields;

    /** @var string
        primary key name! */
    private $pk;

    /** @var Logger
        a Logger instance */
    protected $logger;

    // {{{ static members

    /** @var Connection
        database connection*/
    protected static $conn;

    /** @var string
        current table name */
    protected static $table_name;

    // }}}

    /**
     * Establish A Database Connection
     */
    public static function establish_connection () {
        if (self::$conn === NULL) {
            self::$conn = Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn());
        }
    }

    /**
     * Force a closing of the database connection
     */
    public static function close() {
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
        $this->logger= Registry::get('__logger');
        self::establish_connection();
        $this->fields = new FieldsAggregate();
        self::$table_name = Inflector::pluralize(strtolower(get_class($this)));
        $table_info = self::$conn->getDatabaseInfo()->getTable(self::$table_name);
        $this->pk = $table_info->getPrimaryKey()->getName();

        foreach( $table_info->getColumns() as $col) {
            $field = new Field( $col->getName() );
            // $field->size = $col->getSize();
            $field->type = CreoleTypes::getCreoleName( $col->getType() ) ;
            // $field->formattedName =  str_replace( '_', ' ', $col->getName() );
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
            $this->fields->add( $field );
        }

        if ( !empty($params) ) {
            foreach ($params as $field_name => $field_value) {
                $this->$field_name = $field_value;
            }
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
        for($it = $this->fields->getIterator(); $it->valid(); $it->next()) {
            if ($it->current()->getName() == $field_name) {
                $it->current()->setValue($field_value);
                $it->current()->isAffected = TRUE;
                $this->fields->setAffected(TRUE);
                return;
            }
        }
        throw new ActiveRecordException ('Cannot Set the value of field: ' . $field_name . '. No such field!');
    }

    /**
     * It gets the value of the field
     * @see http://php.net/manual/en/language.oop5.overloading.php
     * @param string, field_name, the field name
     * @throw ActiveRecordException
     * @return field value
     */
    public function __get($field_name) {
        for($it = $this->fields->getIterator(); $it->valid(); $it->next()) {
            if ( $it->current()->getName() == $field_name ) {
                return $it->current()->isAffected ? $it->current()->getValue() : NULL;
            }
        }
        // Associations:
        // 1. has_one, the syntax: protected $has_one= array('__FIELD_NAME__');
        if (isset($this->has_one) && $this->has_one && in_array($field_name, $this->has_one)) {
            $fk= $field_name.'_id';
            for($it = $this->fields->getIterator(); $it->valid(); $it->next()) {
                if ( $it->current()->getName() == $fk ) {
                    $_table= Inflector::singularize(self::$table_name);
                    self::setTable($field_name);
                    $ret= self::__find(array($it->current()->getValue()));
                    self::setTable($_table);
                    return $ret;
                }
            }
        }
        // 2. has_and_belongs_to_many
        if (
            (isset($this->has_and_belongs_to_many))
              &&
              (
                  (
                      (is_array($this->has_and_belongs_to_many))
                      &&
                      (in_array($field_name, $this->has_and_belongs_to_many))
                  )
                  ||
                  (
                      $this->has_and_belongs_to_many == Inflector::pluralize($field_name)
                  )
              )
            )
        {
            $assoc = new HasAndBelongsToManyAssociation();
            $assoc->owner= Inflector::singularize(self::$table_name);
            $assoc->pk   = $this->fields->getPrimaryKey()->getValue();
            $assoc->class= Inflector::singularize($field_name);
            return $assoc->execute();
        }
        throw new ActiveRecordException ('Cannot Get the value of filed: `' . $field_name . '`. No such filed!');
    }

    /** removes some duplicate code */
    public function __call($method, $arguments) {
        if ($method == 'destroy') return $this->delete();
        $know_methods = array('save', 'insert', 'update', 'delete');
        if (!in_array($method, $know_methods)) {
            trigger_error(sprintf('Call to undefined method: %s::%s().', $this->getClassName(), $method), E_USER_ERROR);
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
    // }}}

    // {{{ save
    /**
     * Save,
     *    will do a SQL Insert and return the last_inserted_id or an Update returning the number of affected rows.
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
        if ($this->fields->getPrimaryKey()->isAffected) {
            $sql = $this->getUpdateSql();
        } else {
            $sql = $this->getInsertSql();
        }
        $af_rows = $this->performQuery($sql);
        $id = $this->getNextId();
        return $id ? $id : $af_rows;
    }
    // }}}

    // {{{ insert
    public function insert() {
        $af_rows = $this->performQuery($this->getInsertSql());
        $id = $this->getNextId();
        return $id ? $id : $af_rows;
    }
    // }}}

    // {{{ update
    public function update() {
        return $this->performQuery($this->getUpdateSql());
    }
    // }}}

    // {{{ delete
    public function delete() {
        $whereClause = array();
        foreach ($this->fields->getAffectedFields() as $col) {
            $whereClause[] = $col->getName() . ' = ? ';
        }
        $sql = 'DELETE FROM ' . self::$table_name . ' WHERE ' . implode(' AND ', $whereClause);
        return $this->performQuery($sql);
    }
    // }}}

    // {{{ private methods (internal helpers)
    private function getNextId() {
        if ($this->fields->getPrimaryKey() !== NULL) {
            $_pk = $this->pk;
            $id  = self::$conn->getIdGenerator()->getId($this->pk);
            $this->$_pk = $id;
            return $id;
        } else {
            return FALSE;
        }
    }

    private function performQuery($sql) {
        $stmt = self::$conn->prepareStatement($sql);
        self::populateStmtValues($stmt, $this->fields->getAffectedFields());
        $af_rows = $stmt->executeUpdate();
        $stmt->close();
        Registry::get('__logger')->debug('Performing sql query: ' . self::$conn->lastQuery);
        // $this->_reset();
        return $af_rows;
    }

    /** resets affected flag, this method is not used yet! */
    private function _reset() {
        foreach ($this->fields->getAffectedFields() AS $field) {
            $field->isAffected = FALSE;
        }
    }

    /** FIXME:
     * <tt>UPDATE __TABLE__ SET foo='12' WHERE bar='ee';</tt>
     * is not working.
     */
    private function getUpdateSql() {
        $sqlSnippet = '';
        if ($this->pk !== NULL) {
            $sqlSnippet = ' WHERE ' . $this->pk . ' = ' . $this->fields->getPrimaryKey()->getValue();
        }
        $sql = 'UPDATE ' . self::$table_name . ' SET ';
        foreach($this->fields->getAffectedFields() as $field) {
            $sql .= $field->getName() . ' = ?, ';
        }
        return substr($sql, 0, -2) . $sqlSnippet;
    }

    private function getInsertSql() {
        return 'INSERT INTO ' . self::$table_name
               . ' (' . implode(',', $this->fields->getAffectedFieldsNames()) . ')'
               . ' VALUES (' . substr(str_repeat('?,', count($this->fields->getAffectedFields())), 0, -1) . ')';
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

    // {{{ find monster
    /**
     *
     * @throws ActiveRecordException if a requested case is not yet implemented (or invalid)
     * @throws RecordNotFoundException no record responded to this method
     */
    public static final function __find($params= array()) {
        $numargs = sizeof($params);
        if($numargs == 0) return self::__find(array('all'));
        self::establish_connection();

        $class = new ReflectionClass(Inflector::singularize(ucfirst(self::$table_name)));
        $query = new QuerryBuilder(self::$table_name);

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
            $stmt = self::$conn->prepareStatement($query->buildQuery());
            $stmt->setInt(1, $params[0]);
            $rs   = $stmt->executeQuery();
            if ($rs->getRecordCount() == 1) {
                $rs->next();
                $stmt->close();
                return $class->newInstance($rs->getRow());
            } elseif ($rs->getRecordCount() == 0) {
                throw new RecordNotFoundException(
                    'Couldn\'t find a `' . Inflector::singularize(ucfirst(self::$table_name)) . '` with ID=' . $params[0]);
            }
        } elseif(is_array($params[0])) {
            $query->addArray($params[0]);
        } else {
            throw new ActiveRecordException('Case Not Implemented yet!');
        }

        $stmt = self::$conn->prepareStatement($query->buildQuery());
        // add limit and/or offset if requested
        if ($limit = $query->getLimit())   $stmt->setLimit($limit);
        if ($offset = $query->getOffset()) $stmt->setOffset($offset);
        $rs = $stmt->executeQuery();
        if ($rs->getRecordCount() == 0) {
            throw new RecordNotFoundException('Couldn\'t find a ' . Inflector::singularize(ucfirst(self::$table_name)));
        }
        // build a list with objects of this type
        $results = new RowsAggregate();
        while ($rs->next()) {
            $results->add($class->newInstance($rs->getRow()));
        }
        $rs->close();
        $stmt->close();
        Registry::get('__logger')->debug('Performed sql query: ' . self::$conn->lastQuery);
        return $results;
    }
    // }}}

    private static function hasResults(ResultSet $rs, $conditions) {
        if ($rs->getRecordCount() == 0) {
            throw new RecordNotFoundException(
                'Couldn\'t find a `' . Inflector::singularize(ucfirst(self::$table_name)) .'` matching ' . $conditions);
        }
    }

    /**
     * Sets the current table name.
     *
     * The name is pluralized and to lower case
     * @param string table name
     */
    public static function setTable($table) {
        self::$table_name = Inflector::pluralize(strtolower($table));
    }
}
