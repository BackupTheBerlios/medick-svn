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

include_once('active/record/FieldsAggregate.php');
include_once('active/record/RowsAggregate.php');
include_once('active/record/QueryBuilder.php');
include_once('active/support/Inflector.php');
include_once('creole/Creole.php');

class ActiveRecordException extends Exception {     }

class ActiveRecordBase {

    /** DB Table Fields */
    protected $fields;

    /** pk. name! */
    private $pk;
    
    // {{{ static members
    /** database connection*/
    protected static $conn;
    /** table name */
    protected static $table_name;
    // }}}

    public static function establish_connection () {
        if (self::$conn === NULL) {
            self::$conn = Creole::getConnection(Configurator::getInstance()->getDatabaseDsn());
        }
    }
    
    public final function __construct($params = array()) {
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
            foreach ($params AS $field_name=>$field_value) {
                $this->$field_name = $field_value;
            }
        }

    }

    // {{{ __magic
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

    public function __get($field_name) {
        for($it = $this->fields->getIterator(); $it->valid(); $it->next()) {
            if ( $it->current()->getName() == $field_name ) {
                return $it->current()->isAffected ? $it->current()->getValue() : NULL;
            }
        }
        throw new ActiveRecordException ('Cannot Get the value of filed: ' . $filed_name . '. No such filed!');
    }
    
    /** removes some duplicate code */
    public function __call($method, $arguments) {
        if ($method == 'destroy') return $this->delete();
        $know_methods = array('save', 'insert', 'update', 'delete');
        if (!in_array($method, $know_methods)) {
            trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $method), E_USER_ERROR);
        } elseif(!$this->fields->hasAffected()) {
            throw new ActiveRecordException('No field was set before ' . $method);
        } else {
            $this->$method($arguments[0]);
        }
    }

    /** returns a string representation of this object */
    public function __toString() {
        $string = '';
        foreach ($this->fields->getAffectedFields() AS $field) {
            $string .= "[ " . $field->type . " ] " . $field->getName() . " : " . $field->getValue() . "\n";
        }
        return $string;
    }
    // }}}
    
    
    // {{{ save
    public function save() {
        if ($this->fields->getPrimaryKey()->isAffected) {
            $sql = $this->getUpdateSql();
        } else {
            $sql = $this->getInsertSql();
        }
        $af_rows = $this->_perform($sql);
        if( $this->fields->getPrimaryKey() !== NULL ) {
        	$id = self::$conn->getIdGenerator()->getId($this->pk);
            $_pk = $this->pk;
            $this->$_pk = $id;
            return $id ? $id : $af_rows;
        } else {
            return $af_rows;
        }
    }
    // }}}

    // {{{ insert
    public function insert() {
        $af_rows = $this->_perform($this->getInsertSql());
        if ($this->fields->getPrimaryKey() !== NULL) {
            $id = self::$conn->getIdGenerator()->getId($this->pk);
            $_pk = $this->pk;
            $this->$_pk = $id;
            return $id ? $id : $af_rows;
        } else {
            return $af_rows;
        }

    }
    // }}}
    // {{{ update
    public function update() {
        return $this->_perform($this->getUpdateSql());
    }
    // }}}
    // {{{ delete
    public function delete() {
        $whereClause = array();
        foreach ($this->fields->getAffectedFields() as $col) {
            $whereClause[] = $col->getName() . ' = ? ';
        }
        $sql = 'DELETE FROM ' . self::$table_name . ' WHERE ' . implode(' AND ', $whereClause);
        return $this->_perform($sql);
    }
    public function destroy() {

    }
    // }}}

    private function _perform($sql) {
        $stmt = self::$conn->prepareStatement($sql);
        self::populateStmtValues($stmt, $this->fields->getAffectedFields());
        $af_rows = $stmt->executeUpdate();
        $stmt->close();
        // TODO: replace with logger.
        echo "Performing: " . self::$conn->lastQuery . "\n";
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
            $sql .= $field->getName() . ' = ?,';
        }
        return substr($sql, 0, -1) . $sqlSnippet;
        
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
        foreach($fields AS $field) {
            if($field->getValue() === NULL){
                $stmt->setNull($i++);
            } else {
                $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::getCreoleCode(strtoupper($field->type)));
                $stmt->$setter($i++, $field->getValue());
            }
        }
    }

    // {{{ find monster
    public static function find() {
        $numargs = func_num_args();
        if($numargs == 0) return self::find('all');
        $params = func_get_args();

        // $class = new ReflectionClass(Inflector::singularize(ucfirst(self::$table_name)));
        $_klazz = Inflector::singularize(ucfirst(self::$table_name));
        
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
                return new $_klazz($rs->getRow());
            }
        } else {
            throw new ActiveRecordException('Case Not Implemented yet!');
        }

        $stmt = self::$conn->prepareStatement($query->buildQuery());
        // add limit and/or offset if requested
        if ($limit = $query->getLimit())   $stmt->setLimit($limit);
        if ($offset = $query->getOffset()) $stmt->setOffset($offset);
        $rs = $stmt->executeQuery();
        // build a list with objects of this type
        $results = new RowsAggregate();
        while ($rs->next()) {
            // $results->add($class->newInstance($rs->getRow()));
            $results->add(new $_klazz($rs->getRow()));
        }
        // todo: log.
        echo "Performing: " . self::$conn->lastQuery . "\n";
        
        return $results;
    }
    // }}}

    public static function setTable($table) {
        self::$table_name = Inflector::pluralize(strtolower($table));
    }
    
}

