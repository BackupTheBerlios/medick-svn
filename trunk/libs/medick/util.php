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

// {{{ ICollection
/**
 * Base interface for Medick Collections
 *
 * A Collection for medick framework acts as an array witch holds numeric keys
 * with medick.Object as values.
 *
 * @package locknet7.medick.util
 */
interface ICollection extends ArrayAccess {

    /**
     * Gets the size of this collection
     *
     * @return int the size of this Collection
     */
    public function size();

    /**
     * Clear the contents of this Collection
     *
     * @return void
     */
    public function clear();

    /**
     * Gets an iterator over this Collection
     *
     * @return medick.util.IIterator
     */
    public function iterator();

    /**
     * Adds an Object into this Collection
     *
     * @param medick.Object obj the Object to add
     * @return
     */
    public function add(Object $obj);

    /**
     * Removes the Object from the Collection
     *
     * @param medick.Object obj the Object to remove
     * @return
     */
    public function remove(Object $obj);

    /**
     * Check if this Collection contains the given Object
     *
     * @param medick.Object obj the Object to look for
     * @return bool, TRUE if the Object is in the collection
     */
    public function contains(Object $obj);
}
// }}}

// {{{ IIterator
/**
 * Medick Iterator Interface.
 *
 * An Iterator over a Collection
 * @package locknet7.medick.util
 */
interface IIterator {

    /**
     * Check if this Collection has more elements.
     *
     * @return bool, true if the call
     */
    public function hasNext();

    /**
     * It gets the next element in this Collection.
     * NOTE: the internal pointer is moved to the next element.
     * @return medick.Object
     */
    public function next();

    /**
     * It gets the previous element of this Collection
     *
     * NOTE: the internal pointer is moved to the previous element
     * @return medick.Object
     */
    public function prev();

    /**
     * It gets the key of the current element
     *
     * NOTE: a call to next should be invoked before using this method
     */
    public function key();
}
// }}}

// {{{ Collection
/**
 * Default implementation for medick collections
 *
 * <code>
 *   class Foo extends Object {  }
 *   class Bar extends Object {  }
 *   class Baz extends Object {  }
 *   $col= new Collection();
 *   $col[] = new Foo();   // $col->size()=1 (Foo object is added into collection)
 *   $col->add(new Foo()); // $col->size()=2 (Foo is added again since is a new instance)
 *   $b= new Bar ();
 *   $col->add($b); // $col->size()=3; // $b (Bar) is added
 *   $col[] = $b;   // $col->size()=3; // $b is already in the Collection)
 *   $col[] = new Bar(); // $col->size()=4; (Bar is added, a new instance)
 *   $col->contains(new Baz()); // false (Baz waz not added)
 *   $col->contains($b); // true ($b is there)
 *   $col->contains(new Bar()); // false (is a new instance)
 * </code>
 *
 * @package locknet7.medick.util
 */
class Collection extends Object implements ICollection {

    /**
     * Collection elements.
     * @var array
     */
    protected $elements = array();

    /**
     * Creates a new Collection from the provided array
     *
     * @param array elements defaults to an empty array
     */
    public function Collection($elements=array()) {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa0
     * @param int offset
     * @return boot, TRUE if it exists.
     */
    public function offsetExists($offset) {
        return isset($this->elements[$offset]);
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa1
     * @param int offset
     * @throws NoSuchElementException when the offset is invalid
     */
    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->elements[$offset];
        }
        throw new NoSuchElementException('Invalid offset: ' . $offset);
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa2
     * @param int offset
     * @param medick.Object item the Object to add
     * @throws InvalidArgumentException
     *      when the offset is not an integer
     *      when the item is not a medick.Object
     */
    public function offsetSet($offset, $item) {
        if (!$item instanceof Object) {
            throw new InvalidArgumentException('Item should be an medick.Object');
        }
        if (!is_null($offset)) {
            throw new InvalidArgumentException($this->getClassName() . ' accepts only NULL keys!');
        }
        if ($this->contains($item)) {
            return FALSE;
        }
        return $this->add($item);
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see: http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html#ArrayAccessa3
     * @see: Collection::removeAt()
     * @param int offset
     */
    public function offsetUnset($offset) {
        $this->removeAt($offset);
    }

    /**
     * @see locknet7.medick.util.ICollection::add(locknet7.medick.Object)
     * @throws IllegalStateException when Collection.onAddObject returns FALSE
     */
    public function add(Object $object) {
        if ($this->onAdd($object)) {
            return $this->elements[]= $object;
        }
        throw new IllegalStateException('onAdd failed!');
    }

    /**
     * @see locknet7.medick.util.ICollection::clear()
     */
    public function clear() {
        foreach ($this->elements as $element) {
            $this->onRemove($element);
        }
        $this->elements= array();
    }

    /**
     * @see locknet7.medick.util.ICollection.contains(medick.Object)
     */
    public function contains(Object $obj) {
        return $this->indexOf($obj) >= 0;
    }

    /**
     * Check if this Collection isEmpty
     *
     * @return bool, TRUE if the Collection is empty
     */
    public function isEmpty() {
        return $this->size() > 0;
    }

    /**
     * @see medick.util.ICollection.remove(medick.Object)
     */
    public function remove(Object $obj) {
        if( ($index=$this->indexOf($obj))>=0 ) {
            return $this->removeAt($index);
        }
        return FALSE;
    }

    /**
     * Removes the element at the given index.
     *
     * @param int index the index
     * @return TRUE if the element was succesfully removed
     * @throws NoSuchElementException when there is no element at the specified position
     */
    public function removeAt($index) {
        if ($this->offsetExists($index)) {
            $this->onRemove($this->elements[$index]);
            array_splice($this->elements,$index,1);
            return TRUE;
        }
        throw new NoSuchElementException('Invalid offset: ' . $offset);
    }

    /**
     * It gets the index of the specified Object
     *
     * @param medick.Object obj the Object to get the index for.
     * @return int, -1
     */
    public function indexOf(Object $obj) {
        $pos= array_search($obj, $this->elements, TRUE);
        if ($pos === FALSE) {
            $pos = -1;
        }
        return $pos;
    }

    /**
     * see ICollection.size()
     */
    public function size() {
        return count($this->elements);
    }

    /**
     * @see ICollection.size()
     */
    public function iterator() {
        return new CollectionIterator($this);
    }

    /**
     * Returns the underlying array of this Collection
     *
     * @return array
     */
    public function toArray() {
        return $this->elements;
    }

    /**
     * Callback method called when a new item is added into Collection
     *
     * It returns TRUE in this default implementation
     * Overwrite this method to provide further functionality to your Collection
     * @return bool, TRUE if we allow this item in the Collection
     */
    public function onAdd(Object $object) {
        return TRUE;
    }

    /**
     * Callback method called when an item is removed from Collection
     *
     * Overwrite this method to provide further functionality to your Collection
     * @return void
     */
    public function onRemove(Object $object) {  }

}
// }}}

// {{{ CollectionIterator
/**
 * An Iterator implementation over a ICollection
 *
 * @package medick.util
 * <code>
 *    $it=$col->iterator();
 *    while ($it->hasNext()) {
 *      $it->next(); // returns the medick.Object and moves to the next element.
 *    }
 * </code>
 */
class CollectionIterator extends Object implements IIterator {

    /**
     * Collection to loop on
     *
     * @var ICollection
     */
    protected $collection;

    /**
     * Internal pointer to Collection elements
     *
     * @var int
     */
    private $idx;

    /**
     * Creates a new CollectionIterator on the given Collection
     */
    public function CollectionIterator(ICollection $collection) {
        $this->collection = $collection;
        $this->idx = 0;
    }

    /**
     * @see locknet7.medick.util.IIterator::hasNext()
     */
    public function hasNext() {
        return $this->collection->size() > $this->idx;
    }

    /**
     * @see locknet7.medick.util.IIterator::next()
     */
    public function next() {
        return $this->collection[$this->idx++];
    }

    /**
     * @see locknet7.medick.util.IIterator::prev()
     */
    public function prev() {
        return $this->collection[$this->idx--];
    }

    /**
     * @see locknet7.medick.util.IIterator::key()
     */
    public function key() {
        $index = (int)($this->idx-1);
        if ($index < 0) {
            throw new IllegalStateException(
                'Call ' . $this->getClassName() . '::next() method before ' . $this->getClassName() . '::key().');
        }
        return $index;
    }
}
// }}}
