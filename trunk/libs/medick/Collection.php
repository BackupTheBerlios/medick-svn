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

/**
 * @package locknet7.medick
 */
class Collection extends Object implements ArrayAccess {

    /** @var array
        Collection container */
    protected $list  = array();
    
    /** @var string
        Type of objects that we want to store in this Collection */
    protected $type = NULL;
    
    /** Creates a new Collection */
    public function __construct() {
        $this->type= 'Object';
    }
    
    /**
     * Check if the given offset exists
     * @param int offset, offset indentifier
     * @return TRUE if the offset exists, FALSE otherwise
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa0
     */
    public function offsetExists($offset) {
        return isset($this->list[$offset]);
    }
    
    /**
     * It gets the current element by his offset
     * @param int offset of the element to get
     * @return FALSE if the given offset don`t exists or Object, the element
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa1
     */
    public function offsetGet($offset) {
        if (!$this->offsetExists($offset)) throw new MedickException ('Array Index Out of Bounds!');
        return $this->list[$offset];
    }
    
    /**
     * It sets the element by his offset
     * @param int offset the position where we want to insert the element, if NULL is given, the elemnt will be inserted on the last position
     * @param Object value of this element, overwrite the type propriety to set a diffrent type of Object in child classes
     * @throws MedickException if the value is not an instance of the specified type
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa2
     * @return Object, the added element
     */
    public function offsetSet($offset, $value) {
        if (!$value instanceof $this->type) {
            throw new MedickException('Illegal Argument!');
        }
        if ($offset===NULL) {
            $this->list[] = $value;
        } else {
            $this->list[$offset]= $value;
        }
        return $value;
    }
    
    /**
     * unset the element from the specified position
     * @param int the element position
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa3
     * @return Object the removed element
     */
    public function offsetUnset($offset) {
        if (!$this->offsetExists($offset)) throw new MedickException ('Array Index Out of Bounds!');
        $element= $this->offsetGet($offset);
        unset($this->list[$offset]);
        $this->list= array_values($this->list);
        return $element;
    }
    
    /**
     * It gets this collection iterator
     * @return ArrayIterator
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceIteratorAggregate.html#IteratorAggregatea0
     * @see: http://www.php.net/~helly/php/ext/spl/classArrayIterator.html
     */
    public function getIterator() {
        return new ArrayIterator($this->list);
    }
    
    /** wrapper for offsetSet */
    public function add($element) {
        $this->offsetSet(NULL, $element);
        return $element;
    }
    
    /**
     * Add another Collection in to this
     *  <code>
     *      $col1 = new MyCollection();
     *      $col1[] = new Foo();
     *      $col1[] = new Bar();
     *      $col2 = new MyOtherCollection();
     *      $col2[] = new Baz();
     *      $col2[] = new Foo();
     *      $col1->addAll($col2);
     *      $col1->size(); // outputs 4
     *  </code>
     * @param ICollection
     * @return void
     */
    public function addAll(Collection $ic) {
        for($it = $ic->getIterator(); $it->valid(); $it->next()) {
            $this->add($it->current());
        }
    }
    
    /** alias for offsetSet */
    public function set($index, $element) {
        $this->offsetSet($index, $element);
    }
    /** alias for offsetGet */
    public function get($index) {
        return $this->offsetGet($index);
    }
    
    /** Check if the given object is in this collection */
    public function contains(Object $o) {
        return in_array($o, $this->list, TRUE);
    }

    /** Check if this collection is empty */
    public function isEmpty() {
        return $this->size() == 0;
    }
    
    /** Removes the specified Object from this Collection */
    public function remove(Object $o) { 
        for($it = $this->getIterator(); $it->valid(); $it->next()) {
            if ($it->current()->getClassName() == $o->getClassName()) {
                $element= $it->current();
                $this->offsetUnset($it->key());
                return $element;            
            }
        }
        return FALSE;
    }
    
    /** counts the elements of this collection */
    public function size() {
        return count($this->list);
    }
    /** alias for size */
    public function count() {
        return $this->size();    
    }
}
