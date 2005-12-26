<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
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
//////////////////////////////////////////////////////////////////////////////////
// }}}

// {{{ ICollection
/**
 * Base interface for medick Collections
 *
 * A Collection for medick framework is an array witch holds numeric 
 * keys with Objects as values
 * 
 * @package locknet7.medick.util
 */
interface ICollection {
    
    /** 
     * Adds a new Object into this Collection
     * @param medick.Object
     * @return Object, the Object just added.
     */ 
    function add(Object $o);
    
    /**
     * Removes the Object from this collection
     * @param medick.Object the Object we want to remove
     * @return Object, the Object just removed
     */ 
    // function remove(Object $o);
    
    /**
     * Removes all the objects stored in this Collection
     * @return void
     */
    // function clear();
    
    /**
     * Indicates the size of this Collection
     * @return int the size
     */
    function size();

    /**
     * Check if this Collection is empty
     * @return bool, TRUE if this Collection is empty, FALSE otherwise
     */
    function isEmpty();

    /**
     * It gets the current iterator associated with this collection
     * @return medick.util.IIterator
     */
    function iterator(); 
    
    /**
     * It gets a PHP Array representation of this collection
     * @return array
     */
    function toArray();
    
    /**
     * Returns true if this collection contains the specified element
     * @return bool
     */
    // function contains(Object $o);
    
}
// }}}

// {{{ IIterator
/**
 * An iterator over a Collection
 * 
 * @package locknet7.medick.util
 */
interface IIterator {

    /**
     * Check if this Collection has more elements
     *
     * @return TRUE if this Iterator has a next element, 
     *         FALSE if we are at the last element
     */
    function hasNext();

    /**
     * It gets the current element
     *
     * @return medick.Object
     */
    function next();
    
    /**
     * It gets the current element.
     * 
     * @return medick.Object
     */
    function current();

    /**
     * It gets the current element key
     *
     * @return int
     */
    function key();
    
}
// }}}


// {{{ AbstractCollection
abstract class AbstractCollection extends Object implements ICollection {

    private $elements;

    public function AbstractCollection(Array $elements=array()) {
        $this->elements= $elements;
    }

    public function iterator() {
        return new MedickIterator($this);
    }

    public function add(Object $object) {
        $this->elements[]= $object;
    }

    public function size() {
        return count($this->elements);
    }

    public function isEmpty() {
        return $this->size() == 0;
    }

    public function get($idx) {
        return $this->elements[(int)$idx];
    }

    public function toArray() {
        return $this->elements;
    }

}
// }}}

// {{{ MedickIterator
class MedickIterator implements IIterator {

    private $collection;

    // private $elements;

    private $idx=0;

    public function MedickIterator(ICollection $collection) {
        $this->collection = $collection;
        // $this->elements   = $collection->toArray();
        // reset($this->elements);
    }

    public function hasNext() {
        return $this->collection->size() > $this->idx;
    }

    public function next() {
        // return $this->elements[$this->idx++];
        return $this->collection->get($this->idx++);
    }

    public function current() {
        // return $this->elements[(int)($this->idx - 1)];
        return $this->collection->get((int)($this->idx - 1));
    }

    public function key() {
        return (int)($this->idx-1);
    }

}
// }}}
