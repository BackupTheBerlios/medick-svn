<?php

/**
 * This file is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */

class Todo extends ActiveRecordBase {

    public static function find_done() {
        try {
            return Todo::find('all', array('condition'=>'done=1'));
        } catch (RecordNotFoundException $rnfEx) {

        }
    }

    public static function find_not_done() {
        try {
            return Todo::find('all', array('condition'=>'done=0'));
        } catch (RecordNotFoundException $rnfEx) {

        }
    }

    public static function find() {
        $args= func_get_args();
        self::setTable(__CLASS__);
        return self::__find($args);
    }

}

