<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
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
 * It knows how to create an SQLCommand from an array
 *
 *
 * Arguments:
 *
 *  1. Simple, no argument:
 *    1.1 User::find(); // select * from users; // => RowsAggregate
 *
 *  2. Simple, one Argument
 *    2.1. User::find( 'all' ); // => select * from users; // => RowsAggregate
 *    2.2. User::find( 'first' ); // => select * from users limit 1; // => ActiveRecord
 *    2.3. User::find( 1 ); // => select * fro users where id=1; // => ActiveRecord
 *
 *  3. Clauses, one simple argument (see 2) and an array as the second one
 *    3.1. User::find( 'all', array( 'condition' => "name='Peter'" )); 
 *    // select * from users where name='Peter';
 *    3.2. User::find( 'all', array( 'order by' => 'name asc' ));
 *    // select * from users order by name asc;
 *    3.3. User::find( 'all', array( 'limit' => 5 ));
 *    // select * from users limit 5;
 *    3.4. User::find( 'all', array( 'offset' => 10 ));
 *
 *    3.5. User::find( 'all', array( 'left join' => 'posts on posts.user_id=users.id' ));
 *
 *    3.6. User::find( 'all', array( 'columns' => 'id, name' ));
 *    // select id,name from users;
 *
 *    3.7. User::find( 'all', array( 'from'=>'posts' ));
 *    // select * from users, posts;
 *    3.8. User::find( 'all', array( 'include'=>'posts' ));
 *
 *    3.9. User::find( 'all', array( 
 *          'condition'=>"name != 'Peter'", 'limit' => 5, 'offset'=>10, 'order by' => 'name asc' ));
 *    // select * from users where name != 'Peter' limit 5, 10 order by name asc;
 *
 *  4. Bindings
 *    4.1. User::find( 'all', array('condition'=>'name=?'), array('Peter'));
 *    // select * from users where name='Peter';
 *    4.2. User::find( 'first', array('condition'=>'id > ?'), array(5));
 *    // select * from users where id > 5 limit 1; 
 *
 * @package medick.active.record
 * @author Aurelian Oancea
 */
class SQLBuilder extends Object {

    /** @var string 
        result type owner */
    private $owner;
    
    /** @var array
        clauses */ 
    private $clauses=array();

    /** @var array
        current bindings */ 
    private $bindings=array();
    
    /** @var string
        Type of select (all or first) */
    private $type;
    
    /** @var int 
        limit */
    private $limit;
    
    /** @var int
        offset */
    private $offset;

    /**
     * Constructor.
     * 
     * It parses the arguments and will create the instance variables.
     * Usually this class is a parameter for ActiveRecord::build method, but it is 
     * also used from Associations.
     *
     * @see medick.active.record.ActiveRecord::build, medick.active.record.association
     * @param string owner
     * @param array arguments
     */ 
    public function SQLBuilder($owner, $arguments) {
        $this->owner= $owner;
        if ( !count($arguments) || $arguments[0] == 'all' ) {
            $this->type= 'all';
        } else {
            $this->type = 'first';
            $this->limit= 1;
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
    
    /**
     * It gets the limit
     *
     * @return int limit
     */ 
    public function getLimit() {
        return $this->limit;    
    }

    public function setLimit( $limit ) {
        $this->limit= $limit;
    }

    /**
     * It gets the offset
     *
     * @return int the offset
     */ 
    public function getOffset() {
        return $this->offset;
    }
    
    /**
     * It gets the owner
     *
     * @return string the owner
     */ 
    public function getOwner() {
        return $this->owner;
    }
    
    /**
     * It creates an instance of the owner
     *
     * @return ReflectionClass
     */ 
    // public function getOwnerClass() {
    //     return ActiveRecord::reflect_class( $this->owner );
    // }

    /**
     * It gets the current list of bindings
     *
     * @return array the list of bindings
     */ 
    public function getBindings() {
        return $this->bindings;
    }

    /**
     * It gets the type
     *
     * @return string
     */ 
    public function getType() {
        return $this->type;
    }

    /**
     * Compile an SQLCommand from this query clauses.
     * 
     * Valid Clauses:
     * <ul>
     *  <li>'from'      => to add an additional from clause</li>
     *  <li>'condition' => to insert a sql condition</li>
     *  <li>'order by'  => to set an order by</li>
     *  <li>'columns'   => specify only the columns you want to select (check if it work on aliases too?)</li>
     *  <li>'limit'     => adjust the limit (this is not sended to the SQLCommand since is intended to be used with PreparedStatements)</li>
     *  <li>'offset'    => adds an offset (this is not sended to the SQLCommand since is intended to be used with PreparedStatements)</li>
     *  <li>'left join' => add a left join</li>
     * </ul>
     *
     * @return SQLCommand
     */
    public function compile() {
        if(isset($this->clauses['include'])) {
          $this->clauses['left join'] = Inflector::pluralize($this->clauses['include']) . ' on ' . Inflector::pluralize($this->clauses['include']).'.id='.Inflector::tabelize($this->owner).'.'.$this->clauses['include'].'_id';
        }
        $command= SQLCommand::select()->from(Inflector::tabelize($this->owner));
        if (isset($this->clauses['from']))       $command->from($this->clauses['from']);
        if (isset($this->clauses['condition']))  $command->where($this->clauses['condition']);
        if (isset($this->clauses['order by']))   $command->orderBy($this->clauses['order by']);
        if (isset($this->clauses['columns']))    $command->columns($this->clauses['columns']);
        if (isset($this->clauses['limit']))      $this->limit= $this->clauses['limit'];
        if (isset($this->clauses['offset']))     $this->offset= $this->clauses['offset'];
        if (isset($this->clauses['left join']))  $command->leftJoin('left outer join ' . $this->clauses['left join']);
        return $command;
    }


    public function execute() {
      // 1.
      $stmt= ActiveRecord::connection()->prepare( $this->compile()->getQueryString() );

      // 2. one or more?
      return $stmt->getAllRecords( $this->bindings, new ReflectionClass( $this->owner ) );

      $rs= $stmt->executeQuery( $this->bindings );
      return $rs;
      // 2.
      return array();
    }

}

