<?php

/**
 * This class is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */

class Project extends ActiveRecordBase {

    public function before_insert() {
        $this->created_at= time();
        return TRUE;
    }


    public function before_save() {
        $this->validates()->uniqueness_of('name');
        $this->validates()->presence_of('name', 'description');
        return TRUE;
    }

    public static function find() {
        ActiveRecordBase::initialize(__CLASS__);
        return ActiveRecordBase::__find(func_get_args());
    }

}
