<?php

// $Id$    

include_once('active/record/Base.php');

class Book extends ActiveRecordBase {

    protected $has_one= array('author');
    
    public static function find() {
        ActiveRecordBase::initialize(__CLASS__);
        return ActiveRecordBase::__find(func_get_args());
    }

}
