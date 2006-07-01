<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian[at]locknet[dot]ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Oancea Aurelian nor the names of his contributors may
//   be used to endorse or promote products derived from this software without
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// $Id$
//
// ///////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * Medick ActiveRecord Validation API (2)
 */
 
error_reporting(E_STRICT | E_ALL);

class MedickClass extends ReflectionClass { }

class Object {

    public function getClass() {
        return new MedickClass($this->getClassName());
    }

    public function getClassName() {
        return get_class($this);
    }

    public function __toString() {
        return $this->toString();
    }

    public function toString() {
        return $this->getClassName();
    }

}

class Field extends Object {

    private $name;

    private $value;

    private $errors= array();

    public function Field($name) {
        $this->name= $name;
    }

    public function setValue($value) {
        $this->value= $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function getName() {
        return $this->name;
    }

    public function addError($error) {
        $this->errors[]= $error;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return sizeof($this->errors) > 0;
    }

    public function toString() {
        return $this->value;
    }

}

abstract class Validator extends Object {

    protected $fields;

    protected $message;

    public function message($message) {
        $this->message= $message;
        return $this;
    }

    public function fields(Array $fields=array()) {
        $this->fields= $fields;
    }

    public function validate_each(ActiveRecord $record) {
        foreach($this->fields as $field) {
            $this->validate($record->getField($field));
        }
    }

    abstract public function validate(Field $field);

}

class FormatOfValidator extends Validator {

    protected $message= '%s has invalid format!';

    private $with= '';
    
    public function with($regex='') {
        $this->with= $regex;
        return $this;
    }
    
    public function validate(Field $field) {
        if ( $this->with === '' ||  !preg_match($this->with, $field->getValue())) {
            $field->addError(sprintf($this->message, $field->getName()));
        }
    }

}

class PresenceOfValidator extends Validator {

    protected $message= '%s is empty!';

    public function validate(Field $field) {
        if ($field->getValue() == '') { 
            $field->addError(sprintf($this->message, $field->getName()));
        }
    }

}

class NumericalityOfValidator extends Validator {

    protected $message= '%s is not a number!';

    public function validate(Field $field) {
        $v=$field->getValue();
        if(!((is_numeric($v)) && (intval(0+$v)==$v))) { 
            $field->addError(sprintf($this->message, $field->getName()));
        }
    }

}

class LengthOfValidator extends Validator {

    protected $too_short= '%s is too short, min is %d';
    protected $too_long = '%s is too long, max is %d';
    protected $min= 0;
    protected $max= 1;

    public function in($min, $max) {
        if ( $max <= $min ) { 
            throw new Exception('Doh, you are too smart!');
        }
        $this->min= (int)$min;
        $this->max= (int)$max;
        return $this;
    }

    public function max($max) {
        $this->max= $max;
        return $this;
    }

    public function min($min) {
        $this->min= $min;
        return $this;
    }

    public function too_short($message) {
        $this->too_short= $message;
        return $this;
    }
    
    public function too_long($message) {
        $this->too_long= $message;
        return $this;
    }

    public function validate(Field $field) {
        $l= strlen($field->getValue());
        if ($l < $this->min) { 
            return $field->addError(sprintf($this->too_short, $field->getName(), $this->min));
        } elseif ($l > $this->max) { 
            return $field->addError(sprintf($this->too_long, $field->getName(), $this->max));
        }
    }

}

class ActiveRecord extends Object {

    private $validators= array();

    protected $fields= array();

    public function addValidator(Validator $v) {
        $this->validators[]= $v;
    }

    public function hasField($name) {
        return in_array($name, array_keys($this->fields));
    }

    public function getFields() {
        return $this->fields;
    }

    public function getField($name) {
        return $this->fields[$name];
    }

    public function __set($name, $value) {
        if ($this->hasField($name)) $this->getField($name)->setValue($value);
        else throw new Exception('No such Filed: ' . $name);
    }

    public function __get($name) {
        if ($this->hasField($name)) return $this->getField($name)->getValue();
        else throw new Exception('No such Filed: ' . $name);
    }

    public function __call($method, $args) {
        if (substr($method,0,10)== 'validates_') { 
            $cname= str_replace(" ", "", ucwords(str_replace("_", " ", substr($method, 10)))) . "Validator";
            $validator= new $cname;
            $validator->fields($args);
            $this->validators[]= $validator;
            return $validator;
        }
        if (substr($method,0,7) == 'before_') {
            if ($this->getClass()->hasMethod($method)) return $this->getClass()->getMethod($method)->invoke($this);
            else return true;
        }
        if (substr($method,0,6) == 'after_') {
            if ($this->getClass()->hasMethod($method)) return $this->getClass()->getMethod($method)->invoke($this);
            return;
        }
        trigger_error('Call to a undefined method: ' . $this->getClassName() . '::' . $method, E_USER_ERROR);
    }

    protected function collect_errors() {
        $this->run_validators();
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) return 1;
        }
        return 0;
    }

    private function run_validators() {
        foreach ($this->validators as $v) {
            $v->validate_each($this);
        }
    }
    
    public function save() {
        if ($this->before_save() && $this->collect_errors() === 0) {
            return true;
        }
        return false;
    }

}

class Person extends ActiveRecord {

    public function Person() {
        $this->fields['name']    = new Field('name');
        $this->fields['address'] = new Field('address');
        $this->fields['phone']   = new Field('phone');
        $this->fields['email']   = new Field('email');
    }

    protected function before_save() {
        $this->validates_presence_of('name', 'address')->message('%s should be filled!');
        $this->validates_length_of('name')->in(1,5)->too_short('Too short: %s [min: %d]')->too_long('Too long: %s, [max: %d]');
        $this->validates_length_of('address')->max(5)->too_long('%s is too long, maximum is %d charachters');
        $this->validates_numericality_of('phone');
        $this->validates_format_of('email')->with('/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i');
        return true;
    }

}

class ActiveRecordHelper extends Object {
    
    public static function error_messages_for(ActiveRecord $record) {
        foreach ($record->getFields() as $field) {
            foreach ($field->getErrors() as $error) {
                echo $error . "\n";
            }
        }
    }

}

$p= new Person();
$p->email= 'F';
$p->phone= 'a';
$p->name= 'Marel';
$p->address= 'Andro';
var_dump($p->save());

ActiveRecordHelper::error_messages_for($p);

