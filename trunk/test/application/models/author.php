<?php

// $Id$    

include_once('active/record/Base.php');

class Author extends ActiveRecordBase {
    public static function find() {
        ActiveRecordBase::initialize(__CLASS__);
        return ActiveRecordBase::__find(func_get_args());
    }
}

