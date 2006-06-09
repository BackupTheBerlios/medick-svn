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

/**
 * It represents an sql command
 *
 * You can use this object to build sql query`s in a fancy way:
 * <code>
 *  $command= SQLCommand::select()->from('news')->where('state=?')->orderBy('created_at');
 * // later, you can use a PreparedStatement to bind parameters.
 *  $stmt= $conn->prepareStatement($command->getQueryString());
 *  $stmt->setInt(1, News::PUBLISHED);
 *  $rs= $stmt->executeQuery();
 * </code>
 * More methods will be added later-on, API will be provided on request.
 * 
 * @package medick.active.record
 * @author Oancea Aurelian
 * @since Rev. 343
 */
class SQLCommand extends Object {

    private $command;

    private $tables= array();

    private $joins= array();
    
    private $wheres= array();

    private $orderBy;

    private $columns;
    
    private function SQLCommand($command) {
        $this->command= $command;
    }

    public static function select() {
        return new SQLCommand('select');
    }

    public function from($table) {
        $this->tables[]= $table;
        return $this;
    }

    public function where($clause) {
        $this->wheres[]= $clause;
        return $this;
    }

    public function orderBy($clause) {
        $this->orderBy= $clause;
        return $this;
    }

    public function columns($columns) {
        $this->columns= $columns;
        return $this;
    }
    
    public function leftJoin($what) {
        // $this->tables[]=$what;
        $this->joins[]= $what;
        return $this;
    }
    
    public function getQueryString() {
        $query= $this->command . " ";
        // if ($this->distinct) $query .= "distinct ";
        $query .= $this->appendColumns();
        // $query .= " from " . $this->from;
        $query .= $this->appendFrom();
        $query .= $this->appendJoins();
        $query .= $this->appendWhere();
        $query .= $this->appendOrderBy();
        return $query;
    }

    private function appendColumns() {
        return $this->columns ? $this->columns : "*";
    }

    private function appendFrom() {
        $q= " from ";
        $size= count($this->tables);
        for ($i = 0; $i < $size; ++$i) {
            $q .= $this->tables[$i];
            if ($i <= $size - 2) {
                $q .= " , ";
            }
        }
        return $q;
    }
    
    private function appendJoins() {
        if (count($this->joins)) return " " . implode(" ", $this->joins);
        else return " ";
    }
    
    private function appendWhere() {
        if (count($this->wheres)) return " where " . implode(" and ", $this->wheres);
        else return "";
    }

    private function appendOrderBy() {
        return $this->orderBy ? " order by " . $this->orderBy : "";
    }

}

