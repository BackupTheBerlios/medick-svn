<?php

/**
 * This class is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */

class Project extends ActiveRecordBase {

    public function before_insert() {
        $this->created_at= time();
        $this->validates()->presence_of('name', 'description');
        $this->validates()->uniqueness_of('name');
    }

    public function before_update() {
        $this->validates()->presence_of('name', 'description');
    }

    public static function find() {
        ActiveRecordBase::initialize(__CLASS__);
        return ActiveRecordBase::__find(func_get_args());
    }

}
