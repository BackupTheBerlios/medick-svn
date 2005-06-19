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


class AcriveRecordException extends Exception {     }


class ActiveRecordBase {
	
	/** a Logger instance */
	protected $logger;
	
	/** DB Connection */
	protected static $conn = NULL;
	
	/** Current SQL Stmt */
    protected $stmt = NULL;
	
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
	protected $af_fields;
	
	// {{{ static helpers
	/** injected table name */
	private static $__table = NULL;
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
		// logger:
		$this->logger = Logger::getInstance();
		// connection
		// TODO: a singleton?
	    if(self::$conn === NULL) {
	    	// TODO: configurator.
	        $dsn = array('phptype'=>'mysql','hostspec'=>'localhost','username'=>'root','password'=>'virus','database'=>'todo');
	        self::$conn = self::connect($dsn);
	    }
	    
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
	
	public function __destruct() {  }
	
	
    // {{{ Magic.
    public function __set($name,$value) {
        if(in_array($name,$this->fields)) {
            $this->af_fields[$name] = $value;
        } else {
            throw new ActiveRecordException (
				"Cannot Set the Value for field: " . $name . "\n<br />No such field: " . $name);
        }
    }

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
        if(in_array($this->pk,array_keys($this->af_fields))) {
            $sql = $this->doUpdateSQL();
        } else {
            $sql = $this->doInsertSQL();
        }
        
        $this->stmt = self::$conn->prepareStatement($sql);
        $this->populateStmtValues();
        $af_rows = $this->stmt->executeUpdate();
        
        $this->logger->debug($this->fields);
        $this->logger->debug("Primary Key:: " . $this->pk);
        $this->logger->debug($this->af_fields);
        $this->logger->debug($sql);
        $this->logger->debug(self::$conn->lastQuery);
        
        $this->stmt->close(); // save some resources.
        
        if(!is_null($_pk)) {
        	$keyGen = self::$conn->getIdGenerator();
            $id = $keyGen->getId($this->pk);
            $this->$_pk = $id;
            return $id ? $id : $af_rows;
        }
        // no pk.
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
    
    /** populates stmt values (?,?,?) on sql querys */
    private function populateStmtValues() {
        if(count($this->af_fields) == 0) return;
        $i = 1;
        foreach($this->af_fields AS $field=>$value) {
            if($value === NULL){
                $this->stmt->setNull($i++);
            } else {
                $cMap = $this->tbl_info->getColumn($field);
                if(strtoupper($cMap->getNativeType()) == 'INT') {
                    $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::INTEGER);
                } else {
                    $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::getCreoleCode(strtoupper($cMap->getNativeType())));
                }
                $this->stmt->$setter($i++, $value);
            }
        } // foreach
    }
	
	
	public static function find() {
		$numargs = func_num_args();
        if($numargs == 0) return self::find('all');
        
        // all passed arguments:
        $params = func_get_args();
        
        $class = new ReflectionClass(Inflector::singularize(ucfirst(self::$__table)));       
        
        if ($class->isInstantiable()) {
            if (self::$conn === NULL){
                self::$conn = self::connect(
                        array(
                            'phptype'=>'mysql',
                            'hostspec'=>'localhost',
                            'username'=>'root',
                            'password'=>'virus',
                            'database'=>'todo'));
            }                
            $sql = "SELECT * FROM " . self::$__table;
            
            $stmt = self::$conn->prepareStatement($sql);
            $rs = $stmt->executeQuery();
            $results = new ActiveRecordList();
            foreach($rs as $row) {
                $results->add($class->newInstance($row));
            }
        }
//        
        return $results;
        

	}
	
	public static function find_all() {
		return self::find('all');
	}
	
    /**
     * opens a DB connection 
     * XXX: make this one act like a singleton.
     */
    protected static function connect($dsn) {
    	return Creole::getConnection($dsn);
    }
	
	/** 
	 * Accept the table name injection
	 * TODO: pluralizare. 
	 * 
	 */
    public static function setTable($table) {
    	self::$__table = Inflector::pluralize(strtolower($table));
	}
	
}
