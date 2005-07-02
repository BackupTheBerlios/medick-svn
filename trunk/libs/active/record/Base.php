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
 * @package locknet7.active.record
 */
 
include_once('creole/Creole.php');
include_once('creole/metadata/TableInfo.php');

include_once('active/record/TableInfo.php');
include_once('active/record/List.php');

include_once('active/support/Inflector.php');


class ActiveRecordException extends Exception {     }


class ActiveRecordBase {
	
	/** Table Info */
	private $tbl_info;
	
	/** the table name */
	public $table;
	
	/** primary key field name */
	protected $pk;
	
	/**
     * Name of the Table Fields
     */
    protected $fields = array();
	
	/**
	 * Affected fields during the run
	 */
	protected $af_fields = array();
	
	// {{{ static helpers
	/** injected table name */
    private static $__table = NULL;

    /** DB connection */
    protected static $conn = NULL;
    /** logger instance */
    protected static $logger = NULL;

    /** self instantiated flag
     * is changed to true in <code>ARBase::initialize()</code>
     */
    private static $initialized = FALSE;
	// }}}
	
	/**
	 * Constructor...
	 * Make him final, there is no reason to overwrite in parent classes.
	 * PHP Engine will call the parent constructor.
	 * @param array, params, parameters as pair of `field name` => `value`
	 * 						 if the primary key is touched, will do an update
	 * @return void
	 * @throws SQLException
	 */
	public final function __construct($params = array()) {

	    $this->table = Inflector::pluralize(strtolower(get_class($this)));
	    
	    $this->tbl_info = ARTableInfo::getTableInfo(self::$conn, $this->table);
            
        $this->pk = $this->tbl_info->getPrimaryKey()->getName();
	    
	    foreach($this->tbl_info->getColumns() as $field) {
        	$this->fields[] = $field->getName();
        }

        if(!empty($params)) {
            foreach($params AS $field=>$value) {
                $this->$field = $value;
            }
        }
	    
	}
	
    // {{{ Magic.
    /**
     * It sets the value of the field
     * @see http://uk.php.net/manual/en/language.oop5.overloading.php
     * @param string, name, the field name
     * @param mixed, value, field value
     * @throw ActiveRecordException
     */
    public function __set($name, $value) {
        if(in_array($name,$this->fields)) {
            $this->af_fields[$name] = $value;
        } else {
            throw new ActiveRecordException (
				"Cannot Set the Value for field: " . $name . "\n<br />No such field: " . $name);
        }
    }
    
    /**
     * It gets the value of the field
     * @see http://uk.php.net/manual/en/language.oop5.overloading.php
     * @param string, name, the field name
     * @throw ActiveRecordException
     * @return field value
     */
    public function __get($name) {
        if(in_array($name,$this->fields)) {
            return isset($this->af_fields[$name]) ? $this->af_fields[$name] : NULL;
        } else {
        	throw new ActiveRecordException (
				"Cannot Get the Value for field: " . $name . "\n<br />No such field: " . $name);
        }
    }
    // }}}
	
	
    /**
     * Save, 
     *    will do a SQL Insert and return the last_inserted_id or an Update returning the number of affected rows.
     * <code>
     *      $author = new Author();
     *      $author->name = 'Mihai';
     *      $author->firstName = 'Eminescu';
     *      $id = $author->save(); // will do the insert, returning the ID of the last field inserted.
     *      // a mistake, let`s update.
     *      $author->id = $id;
     *      $author->firstName = 'Sadoveanu';
     *      $author->save(); // performs the update and returns the number of affected rows.
     * </code>
     */
    public function save() {
        if(empty($this->af_fields)) {
        	throw new ActiveRecordException('No field was set before save!');
        }
        
        $_pk = $this->pk;

        // TODO: 1. aditional check`s, if the pk was not set (a select by counting?)
        // TODO: 2. what if we don`t have a pk?
        if (in_array($this->pk, array_keys($this->af_fields))) {
            $sql = $this->doUpdateSQL();
        } else {
            $sql = $this->doInsertSQL();
        }
        
        $stmt = self::$conn->prepareStatement($sql);
        self::populateStmtValues($stmt, $this->tbl_info, $this->af_fields);
        $af_rows = $stmt->executeUpdate();
        
        self::$logger->debug($this->fields);
        self::$logger->debug("Primary Key:: " . $this->pk);
        self::$logger->debug($this->af_fields);
        self::$logger->debug($sql);
        self::$logger->debug(self::$conn->lastQuery);
        
        $stmt->close(); // save some resources.
        if(!is_null($_pk)) {
        	$keyGen = self::$conn->getIdGenerator();
            $id = $keyGen->getId($this->pk);
            $this->$_pk = $id;
            return $id ? $id : $af_rows;
        }
        // no pk.
        return $af_rows;
    }

    /** TODO: params: INT sau ARRAY! */
    public function destroy($params = array()) {
        if (empty($params)) {
            if (empty($this->af_fields)) {
                throw new ActiveRecordException('Foo Is BAR ONCE AGAIN!');
            }
        } else {
            // TODO: a new method.
            foreach($params AS $field=>$value) {
                $this->$field = $value;
            }
        }

        // $whereClause: __nume-camp__= ? 

        $whereClause = array();

        foreach (array_keys($this->af_fields) as $col) {
            $whereClause[] = $col . ' = ? ';
        }
        
        $sql = 'DELETE FROM ' . self::$__table . ' WHERE ' . implode(" AND ", $whereClause);

        $stmt = self::$conn->prepareStatement($sql);
        
        self::populateStmtValues($stmt, $this->tbl_info, $this->af_fields);
        
        $af_rows = $stmt->executeUpdate();
        $stmt->close();
        return $af_rows;
    }
    
    /** SQL Fragment for update */
    private function doUpdateSQL() {
        $sqlSnippet = $this->pk . " = " . $this->af_fields[$this->pk];
        $sql = "UPDATE " . $this->table . " SET ";
        foreach(array_keys($this->af_fields) as $col) {
            $sql .= $col . " = ?,";
        }
        return substr($sql, 0, -1) . " WHERE " . $sqlSnippet;
    }

    /** SQL Fragment for Insert */
    private function doInsertSQL() {
        return "INSERT INTO " . $this->table 
               . " (" . implode(",", array_keys($this->af_fields)) . ")"
               . " VALUES (" . substr(str_repeat("?,", count($this->af_fields)), 0, -1) . ")";
    }
    
    /** populates stmt values (?,?,?) on sql querys
     * @param PreparedStatement, stmt, the prepared statement.
     * @param TableInfo, table_info, info`s about the curent table
     * @param array, fields, the affected fields
     * inspired by Propel::BasePeer::populateStmtValues
     */
    private static function populateStmtValues($stmt, $table_info, $fields) {
        if(count($fields) == 0) return; // -> it should be removed, there is no reason for checking again this thing!
        if (is_null($stmt)) throw new ActiveRecordException('STMT cannot be null!');
        $i = 1;
        foreach($fields AS $field=>$value) {
            if($value === NULL){
                $stmt->setNull($i++);
            } else {
                $cMap = $table_info->getColumn($field);
                if(strtoupper($cMap->getNativeType()) == 'INT') {
                    $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::INTEGER);
                } else {
                    $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::getCreoleCode(strtoupper($cMap->getNativeType())));
                }
                $stmt->$setter($i++, $value);
            }
        }
    }
	
	// {{{ find.
    public static function find() {
		$numargs = func_num_args();
        if($numargs == 0) return self::find('all');

        self::$logger->debug('Nr. of args: ' . $numargs);
        
        // all passed arguments:
        $params = func_get_args();

        // the object (type?) we want to return.
        $class = new ReflectionClass(Inflector::singularize(ucfirst(self::$__table)));               
        if (!$class->isInstantiable()) { // remove this check!
            throw new ActiveRecordException('Model is not instantiable!');
        }
        
        if ($params[0] == 'all' && $numargs == 1 ) {
            // all table fields and one arg.
            $sql = "SELECT * FROM " . self::$__table;

            $stmt = self::$conn->prepareStatement($sql);
            $rs = $stmt->executeQuery();
            // build a list with objects of this type
            $results = new ActiveRecordList();
            while ($rs->next()) {
                $results->add($class->newInstance($rs->getFields()));
            }
            return $results;
        } elseif (is_numeric($params[0]) && $numargs == 1) {
            // one int param, this is the pk. value
            $tbl_info = ARTableInfo::getTableInfo(self::$conn, self::$__table);
            $pk       = $tbl_info->getPrimaryKey()->getName();
            
            $sql  = "SELECT * FROM " . self::$__table . " WHERE " . $pk . " = ?";
            $stmt = self::$conn->prepareStatement($sql);
            $stmt->setInt(1, $params[0]);
            $rs   = $stmt->executeQuery();  
            
            if ($rs->getRecordCount() == 1) {
                $rs->next();
                return $class->newInstance($rs->getFields());
            }
        } elseif ( ($params[0]=='all') && ($numargs == 2) && (is_array($params[1])) ) {
            $sql  = "SELECT * FROM " . self::$__table . " WHERE " . $params[1]['condition'];
            $stmt = self::$conn->prepareStatement($sql);
            $rs = $stmt->executeQuery();
            // build a list with objects of this type
            $results = new ActiveRecordList();
            while ($rs->next()) {
                $results->add($class->newInstance($rs->getFields()));
            }
            return $results;
        }
        else {
            throw new ActiveRecordException('Case not implemented!');
        }
	}
    // }}}
    
    /** an alias for self::find('all') */
	public static function find_all() {
		return self::find('all');
    }
    
    /** returns a ResultSet */
    public static function find_by_sql($stmt) {
        return $stmt->executeQuery();
    }
	
    /**
     * some sort of static instantiator that prepares static members: <code>self::$conn</code> and <code>self::$logger</code>
     * This should be called after setting the table name with
     * <code>ARBase::setTable(__TABLE__NAME__);</code>
     * Is called from ACBase::add_models() via ModelInjector::instaniate()
     */
    public static function initialize() {
        if (self::$initialized) return;
        if (self::$conn   === NULL) self::$conn   = Creole::getConnection(Configurator::getInstance()->getDatabaseDsn());
        if (self::$logger === NULL) self::$logger = Logger::getInstance();
        self::$initialized = TRUE;
    }
    
	/** 
	 * Accept the table name injection
	 */
    public static function setTable($table) {
    	self::$__table = Inflector::pluralize(strtolower($table));
    }

    /** for future use */
    public static function setInheritanceMap($inheritanceMap) {

    }
	
}

