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
 * Container for FiedsAggregate Objects
 * @package locknet7.active.record
 */
class RowsAggregate extends Object implements IteratorAggregate {

    /** @var ArrayObject
        rows container */
    private $container;

    /**
     * Constructor
     */
    public function __construct() {
        $this->container = new ArrayObject();
    }

    /**
     * It adds a new row on this container
     * @param ActiveRecordBase row the row to add into this container
     * @return ActiveRecordBase
     */
    public function add(ActiveRecord $row) {
        $this->container->append($row);
        return $row;
    }

    /**
     * It gets the iterator
     * @return Iterator
     */
    public function getIterator() {
        return $this->container->getIterator();
    }

    /**
     * Count the number of rows in this container
     * @return int
     */
    public function count() {
        return $this->getIterator()->count();
    }

    /**
     * Magick php5 __call
     */
    public function __call($method, $arguments) {
        trigger_error(sprintf('Call to undefined method: %s->%s(%s).', get_class($this), $method,$arguments), E_USER_ERROR);
    }
}
