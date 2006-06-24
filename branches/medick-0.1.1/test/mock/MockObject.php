<?php

// $Id$
    
    class MockObject extends Object {

        private $var;
        
        public function __construct() {
            $this->var= __CLASS__;
        }

        public function getVar() {
            return $this->var;
        }
        
    }

