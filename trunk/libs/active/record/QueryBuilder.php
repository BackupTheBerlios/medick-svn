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

class QuerryBuilder {
    
    private $select  = array();
    private $from;
    private $where   = array();
    private $orderBy;

    private $table;

    private $limit  = FALSE;
    private $offset = FALSE;

    
    public function __construct($table) {
        $this->table = $table;
    }

    public function add($type, $value) {
        switch ($type) {
            case 'include':
                $this->addSelect($value);        
                break;
            case 'condition':
                $this->addWhere($value);
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
                throw new ActiveRecordException ('Call to unknow modifier: ' . $type);
                break;
        }
    }

    public function addArray(array $params) {
        foreach ($params AS $type=>$value) {
            $this->add($type, $value);
        }
    }
    
    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return $this->offset;
    }
    
    public function buildQuery() {
        return  "SELECT "
                 . ($this->select ? implode(" ", $this->select) . " " : " * ")
                 // .implode(", ", $selectClause)
                 // . " FROM " . implode(", ", $fromClause)
                 . " FROM " . $this->table
                 . ($this->where ? " WHERE " . implode(" AND ", $this->where) : "")
                 // .($groupByClause ? " GROUP BY ".implode(",", $groupByClause) : "")
                 // .($havingString ? " HAVING ".$havingString : "")
                 // . ($this->orderBy ? " ORDER BY " . implode(",", $this->orderBy) : "");
                 . ($this->orderBy ? " ORDER BY " . $this->orderBy : "");
                 

    }
    
    public function addSelect($select) {
        $this->select[] = $select;
    }

    public function addWhere($where) {
        $this->where[] = $where;
    }

}

