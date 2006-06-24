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
 * with medick.core.Object as values.
 *
 * @package medick.core
 * @subpackage util
 * @author Oancea Aurelian
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
     */
    public function clear();

    /**
     * Gets an iterator over this Collection
     *
     * @return IIterator
     */
    public function iterator();

    /**
     * Adds an Object into this Collection
     *
     * @param Object obj the Object to add
     */
    public function add(Object $obj);

    /**
     * Removes the Object from the Collection
     *
     * @param Object obj the Object to remove
     */
    public function remove(Object $obj);

    /**
     * Check if this Collection contains the given Object
     *
     * @param Object obj the Object to look for
     * @return bool TRUE if the Object is in the collection
     */
    public function contains(Object $obj);
}
// }}}

// {{{ IIterator
/**
 * Medick Iterator Interface.
 *
 * An Iterator over a Collection
 * @package medick.core
 * @subpackage util
 * @author Oancea Aurelian
 */
interface IIterator {

    /**
     * Check if this Collection has more elements.
     *
     * @return bool TRUE if this collection has more elements
     */
    public function hasNext();

    /**
     * It gets the next element in this Collection.
     *
     * NOTE: the internal pointer is moved to the next element.
     * @return Object
     */
    public function next();

    /**
     * It gets the previous element of this Collection
     *
     * NOTE: the internal pointer is moved to the previous element
     * @return Object
     */
    public function prev();

    /**
     * It gets the key of the current element
     *
     * NOTE: a call to next should be invoked before using this method
     */
    public function key();

    /** Resets the pointer */
    public function reset();

}
// }}}

// {{{ Collection
/**
 * Default implementation for medick collections
 *
 * <code>
 *  class Foo extends Object {  }
 *  class Bar extends Object {  }
 *  class Baz extends Object {  }
 *  $col= new Collection();
 *  $col[] = new Foo();   // $col->size()=1 (Foo object is added into collection)
 *  $col->add(new Foo()); // $col->size()=2 (Foo is added again since is a new instance)
 *  $b= new Bar ();
 *  $col->add($b); // $col->size()=3; // $b (Bar) is added
 *  $col[] = $b;   // $col->size()=3; // $b is already in the Collection)
 *  $col[] = new Bar(); // $col->size()=4; (Bar is added, a new instance)
 *  $col->contains(new Baz()); // false (Baz waz not added)
 *  $col->contains($b); // true ($b is there)
 *  $col->contains(new Bar()); // false (is a new instance)
 * </code>
 *
 * @package medick.core
 * @subpackage util
 * @author Oancea Aurelian
 */
class Collection extends Object implements ICollection {

    /** @var array
        Collection Elements */
    protected $elements = array();

    /**
     * Creates a new Collection from the provided array
     *
     * @param array elements defaults to an empty array
     */
    public function Collection($elements=array()) {
        foreach ($elements as $key=>$element) {
            $this->offsetSet($element, $key);
        }
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html
     * @param int offset
     * @return boot TRUE if it exists.
     */
    public function offsetExists($offset) {
        return isset($this->elements[$offset]);
    }

    /**
     * Method requierd by ArrayAccess interface
     *
     * NOTE: avoid to use this method directly
     * @see http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html
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
     * @see http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html
     * @param int offset
     * @param Object item the Object to add
     * @throws InvalidArgumentException
     *      when the offset is not an integer
     *      when the item is not a Object
     */
    public function offsetSet($offset, $item) {
        if (!$item instanceof Object) {
            throw new InvalidArgumentException('Item should be an medick.core.Object');
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
     * @see http://www.php.net/~helly/php/ext/spl/interfaceArrayAccess.html
     * @see Collection::removeAt()
     * @param int offset
     */
    public function offsetUnset($offset) {
        $this->removeAt($offset);
    }

    /**
     * Adds an Object in this Collection
     *
     * @see ICollection::add()
     * @see ICollection::onAdd()
     * @throws IllegalStateException when Collection::onAdd() returns FALSE
     */
    public function add(Object $object) {
        if ($this->onAdd($object)) {
            return $this->elements[]= $object;
        }
        throw new IllegalStateException('onAdd failed!');
    }

    /** @see ICollection::clear() */
    public function clear() {
        foreach ($this->elements as $element) {
            $this->onRemove($element);
        }
        $this->elements= array();
    }

    /** @see ICollection.contains(Object obj) */
    public function contains(Object $obj) {
        return $this->indexOf($obj) >= 0;
    }

    /**
     * Check if this Collection isEmpty
     *
     * @return bool TRUE if the Collection is empty
     */
    public function isEmpty() {
        return $this->size() == 0;
    }

    /** @see ICollection.remove(Object obj) */
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
     * @param Object obj the Object to get the index for.
     * @return int or int -1 if we can get the index
     */
    public function indexOf(Object $obj) {
        $pos= array_search($obj, $this->elements, TRUE);
        if ($pos === FALSE) {
            $pos = -1;
        }
        return $pos;
    }

    /** @see ICollection::size() */
    public function size() {
        return count($this->elements);
    }

    /** @see ICollection::iterator() */
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
     * Callback method invoked when a new item is added into Collection
     *
     * It returns TRUE in this default implementation.
     * Overwrite this method to provide further functionality to your Collection
     * @return bool TRUE if we allow this item in the Collection
     */
    public function onAdd(Object $object) {
        return TRUE;
    }

    /**
     * Callback method invoked when an item is removed from Collection
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
 * <code>
 *  $it= $col->iterator();
 *  while ($it->hasNext()) {
 *    $current= $it->next(); // returns the medick.Object and moves to the next element.
 *    $current->doSomething(); // invoke a method on the current object
 *  }
 * </code>
 *
 * @package medick.core
 * @subpackage util
 * @author Oancea Aurelian
 */
class CollectionIterator extends Object implements IIterator {

    /** @var ICollection
        Collection to loop on */
    protected $collection;

    /** @var int
        Internal pointer to Collection elements */
    private $idx;

    /**
     * Creates a new CollectionIterator on the given Collection.
     *
     * Sets the iteration pointer to 0
     * @param ICollection collection to iterate on
     */
    public function CollectionIterator(ICollection $collection) {
        $this->collection = $collection;
        $this->idx = 0;
    }

    /** @see IIterator::hasNext() */
    public function hasNext() {
        return $this->collection->size() > $this->idx;
    }

    /** @see IIterator::next() */
    public function next() {
        return $this->collection[$this->idx++];
    }

    /** @see IIterator::prev() */
    public function prev() {
        return $this->collection[$this->idx--];
    }

    /** @see IIterator::key() */
    public function key() {
        $index = (int)($this->idx-1);
        if ($index < 0) {
            throw new IllegalStateException(
                'Call ' . $this->getClassName() . '::next() method before ' . $this->getClassName() . '::key().');
        }
        return $index;
    }

    /** @see IIterator::reset() */
    public function reset() {
        $this->idx=0;
    }
}
// }}}

