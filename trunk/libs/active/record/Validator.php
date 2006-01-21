<?php

class ValidationException extends MedickException {     }

class Validator extends Object {

    private $row;

    public function Validator(DatabaseRow $row) {
        $this->row= $row;
    }

    // pass a separated array list:
    // presence_of('title','body')
    public function presence_of() {
        $args= func_get_args();
        $has_errors= FALSE;
        foreach ($args as $argument) {
            if ($field=$this->row->getFieldByName($argument)) {
                if ($field->getValue() == '') {
                    $field->addError('is empty');
                    $has_errors= TRUE;
                }
            }
        }
        if ($has_errors) {
            throw new ValidationException('Validation failed!');
        } else {
            return TRUE;
        }
    }

    public function uniqueness_of() {
        $args= func_get_args();
        $has_errors= FALSE;
        foreach ($args as $argument) {
            if ($field = $this->row->getFieldByName($argument)) {
                try {
                    ActiveRecordBase::__find(array(
                        array('condition'=>$field->getName() .'=\''.$field->getValue().'\'')));
                    $field->addError('is not unique');
                    $has_errors= TRUE;
                } catch (RecordNotFoundException $rnfEx) {
                    continue;
                }
            }
        }
        if ($has_errors) {
            throw new ValidationException('Validation failed!');
        } else {
            return TRUE;
        }
    }

}
