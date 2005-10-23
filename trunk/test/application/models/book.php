<?php

// $Id$    

include_once('active/record/Base.php');

class Book extends ActiveRecordBase {

    protected $has_one= array('author');
    
    public static function find() {
        $args= func_get_args();
        self::setTable(__CLASS__);
        return self::__find($args);
    }

}
