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
 * It builds Select SQL querys
 * @package locknet7.active.record
 */
class QuerryBuilder extends Object {

    /** @var array
        select, used in include modifier */
    private $select = array();

    /** @var array
        from clause, this includes the table name and joins */
    private $fromClause = array();

    /** @var array
        where clause */
    private $whereClause = array();

    /** @var string
        adds an order by */
    private $orderBy;

    /** @var int limit */
    private $limit  = FALSE;

    /** @var int offset */
    private $offset = FALSE;

    /**
     * Creates a new QueryBuilder
     * @param string table
     */
    public function __construct($table) {
        $this->fromClause[]= $table;
    }

    /**
     * It adds a modifier to this select
     *
     * @param string type of this modifier
     * @param string value of this modifier
     * @throws ActiveRecordException when the type is unknown
     * @return void
     */
    public function add($type, $value) {
        switch ($type) {
            case 'include':
                $this->addSelect($value);        
                break;
            case 'condition':
                $this->addWhere($value);
                break;
            case 'left join':
                $this->addJoin('LEFT', $value);
                break;
            case 'limit':
                $this->limit = (int)$value;
                break;
            case 'offset':
                $this->offset = (int)$value;
                break;
            case 'order':
                $this->orderBy = $value;
                break;
            default:
                throw new ActiveRecordException ('Call to unknow modifier: `' . $type . '`');
                break;
        }
    }

    /**
     * Adds modifiers as array
     * @param array the array of parameters to pass
     */
    public function addArray(/*array*/ $params) {
        foreach ($params AS $type=>$value) {
            $this->add($type, $value);
        }
    }

    /**
     * It gets the limit
     * @return int the limit or FALSE if the limit was not changed
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * It gets the offset
     * @return int the offset or FALSE if the offset was not changed
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * It buils the select query based on the modifiers passed.
     * @return string the sql querys
     */
    public function buildQuery() {
        $query =  "SELECT "
                 . ($this->select ? implode(" ", $this->select) . " " : " * ")
                 // .implode(", ", $selectClause)
                 // . " FROM " . implode(", ", $fromClause)
                 // . " FROM " . $this->table
                 . " FROM " . implode(" ", $this->fromClause)
                 . ($this->whereClause ? " WHERE " . implode(" AND ", $this->whereClause) : "")
                 // .($groupByClause ? " GROUP BY ".implode(",", $groupByClause) : "")
                 // .($havingString ? " HAVING ".$havingString : "")
                 // . ($this->orderBy ? " ORDER BY " . implode(",", $this->orderBy) : "");
                 . ($this->orderBy ? " ORDER BY " . $this->orderBy : "");
        Registry::get('__logger')->debug('Trying to run sql query:');
        Registry::get('__logger')->debug($query);
        return $query;
                 

    }

    // {{{ internal helpers.
    /**
     * Adds a select clause
     * @param string select clause to add
     */
    private function addSelect($select) {
        $this->select[] = $select;
    }

    /**
     * Adds a where clause
     * @param string where clause to add
     */
    private function addWhere($where) {
        $this->whereClause[] = $where;
    }

    /**
     * Adds a join clause
     * @param string join clause to add
     */
    private function addJoin($args, $value) {
        $this->fromClause[] = $args . " JOIN " . $value;
    }
    // }}}
}
