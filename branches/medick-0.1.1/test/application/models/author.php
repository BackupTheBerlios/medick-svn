<?php

// $Id$    

include_once('active/record/Base.php');

class Author extends ActiveRecord {
    public static function find() {
        $args= func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }
}

