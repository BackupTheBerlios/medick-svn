<?php

/**
 * This class is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */

class Project extends ActiveRecordBase {

    public static function find() {
        $args= func_get_args();
        self::setTable(__CLASS__);
        return self::__find($args);
    }

}

