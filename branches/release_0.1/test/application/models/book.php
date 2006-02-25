<?php

// $Id$    

include_once('active/record/Base.php');

class Book extends ActiveRecord {

    protected $has_one= array('author');
    
    public static function find() {
        $args= func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__,$args));
    }

}
