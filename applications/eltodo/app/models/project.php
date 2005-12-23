<?php

/**
 * This class is part of eltodo, medick sample application
 * $Id$
 * @package eltodo.models
 */

class DuplicateRecordException extends MedickException {    }

class Project extends ActiveRecordBase {

    public function before_insert() {
        try {
            $projects=Project::find(array('condition'=>'name=\''.$this->name.'\''));
            throw new DuplicateRecordException($this->name . ' already exists.');
        } catch (RecordNotFoundException $ex) {
            return true;
        }
    }

    public static function find() {
        $args= func_get_args();
        self::setTable(__CLASS__);
        return self::__find($args);
    }

}