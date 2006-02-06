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

/**
 * It knows how to create a SQLCommand from an array
 *
 * @package locknet7.active.record
 */
class QueryBuilder extends Object {

    /** result type owner */
    private $owner;

    private $clauses=array();

    private $bindings=array();
    /** Type of select (all or first) */
    private $type;
    /** limit */
    private $limit;
    /**  offset */
    private $offset;

    public function QueryBuilder($owner, $arguments) {
        $this->owner= $owner;
        if ( !count($arguments) || $arguments[0] == 'all' ) {
            $this->type= 'all';
        } else {
            $this->type = 'first';
        }
        if (isset($arguments[0]) && is_numeric($arguments[0])) {
            $this->clauses['condition']='id=?';
            $this->bindings[]=$arguments[0];
        }
        if (isset($arguments[1])) {
            $this->clauses= $arguments[1];
        }
        if (isset($arguments[2])) {
            $this->bindings= $arguments[2];
        }
    }

    public function getLimit() {
        return $this->limit;    
    }

    public function getOffset() {
        return $this->offset;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function getBindings() {
        return $this->bindings;
    }


    public function getType() {
        return $this->type;
    }

    /**
     * Compile an SQLCommand from this query clauses.
     * 
     * Valid Clauses:
     * <code>
     *  'condition' => to insert a sql condition
     *  'order by'  => to set an order by
     *  'columns'   => specify only the columns you want to select (check if it work on aliases too?)
     *  'limit'     => adjust the limit (this is not sended to the SQLCommand since is intended to be used with PreparedStatements)
     *  'offset'    => adds an offset (this is not sended to the SQLCommand since is intended to be used with PreparedStatements)
     *  'left join' => add a left join
     * </code>
     *
     * @return SQLCommand
     */
    public function compile() {
        $command= SQLCommand::select()->from(Inflector::tabelize($this->owner));
        if (isset($this->clauses['condition']))  $command->where($this->clauses['condition']);
        if (isset($this->clauses['order by']))   $command->orderBy($this->clauses['order by']);
        if (isset($this->clauses['columns']))    $command->columns($this->clauses['columns']);
        if (isset($this->clauses['limit']))      $this->limit= $this->clauses['limit'];
        if (isset($this->clauses['offset']))     $this->offset= $this->clauses['offset'];
        if (isset($this->clauses['left join']))  $command->leftJoin('left outer join ' . $this->clauses['left join']);
        return $command;
    }
}
