<?php
/**
 * This file is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */
class Todo extends ActiveRecordBase {

    public static function find_done() {
        return Todo::find('all', array('condition'=>'done=1'));
    }

    public static function find_not_done() {
        return Todo::find('all', array('condition'=>'done=0'));
    }

    public static function find() {
        $args= func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }

}

