<?php

// $Id$    

include_once('active/record/Base.php');

class Author extends ActiveRecordBase {
    public static function find() {
        $args= func_get_args();
        self::setTable(__CLASS__);
        return self::__find($args);
    }
}

