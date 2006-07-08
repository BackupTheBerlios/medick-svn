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
 * Active Record Validator
 *
 * In medick, validations is performed in ActiveRecord with the help of before_* filters:
 * <code>
 *  class Person extends ActiveRecord {
 *      protected funtion before_save() {
 *          $this->validates_presence_of('name','first_name');
 *          $this->validates_uniqueness_of('phone_no')->message('%s is taken!');
 *          return TRUE; // don't forget to return true, otherwise this object will not be saved in DB!
 *      }
 *  [....]
 * </code>
 * Validators will not throw any exception, but will add errors on the Fields that failed to pass this validation
 * 
 * By abusing on "fluent intefaces" - returning an instance of the same Object in it's methods - we can 
 * accomplish a human readable syntax.<br />
 * Error messages are formatted using PHP sprintf function, with the Field name as the first parameter to this function, so you can use <i>%s</i>
 * to reffer to the field name.<br />
 * 
 * By default, medick gives you validators to perform most regular type of validations.<br />
 * To add your custom validations, you have to extend this class and implement <tt>validate</tt> method.<br />
 * Your class should be named with Validator as a suffix and should be loaded (with include or require) before using it.<br />
 * You can also rewrite the message instance variable to provide a default error message.<br />
 *
 * <code>
 * class AbcOfValidator extends Validator {
 *   
 *   // overwrite default message
 *   protected $message= '%s didnt pass custom ABC validation!';
 *   
 *   // implement validation
 *   public function validate (Field $field) {
 *     if ($field->getValue() != 'abc') {
 *       $field->addError(sprintf($this->message, $field->getFormattedName()));
 *     }
 *   }
 * 
 * }
 *
 * // usage in ActiveRecord before filters:
 * $this->validates_abc_of('name');
 * // error: "Name didnt pass custom ABC validation"
 * // to use a cusstom error message:
 * $this->validates_abc_of('name')->message('%s is not abc');
 * // error will be: "Name is not abc"
 * 
 * </code>
 * 
 * @see ActiveRecord, Field, UniquenessOfValidator, FormatOfValidator, LengthOfValidator, PresenceOfValidator
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */ 
abstract class Validator extends Object {
    
    /** @var array
        names of the fields to be validated */
    protected $fields;
    
    /** @var string
        Error message to be added if the field has errors */ 
    protected $message;
    
    /** @var ActiveRecord
        ActiveRecord */ 
    protected $record= NULL;
    
    /**
     * It sets a custom error message
     *
     * @param string message
     * @return Validator
     */ 
    public function message($message) {
        $this->message= $message;
        return $this;
    }

    /**
     * Sets the fields (names) to perform validation on
     *
     * @param array
     * @return void
     */ 
    public function fields(Array $fields=array()) {
        $this->fields= $fields;
    }

    /**
     * Sets the associated ActiveRecord object
     *
     * @param ActiveRecord
     * @return void
     */ 
    public function record(ActiveRecord $record) {
        $this->record= $record;
    }
    
    /**
     * It validates each Field of this ActiveRecord
     *
     * @return void
     */ 
    public function validate_each() {
        foreach($this->fields as $field) {
            $this->validate($this->record->getField($field));
        }
    }
    
    /**
     * Validate the given Field
     * 
     * @param Field
     * @return void
     */ 
    abstract public function validate(Field $field);

}

/**
 * Validates the format of a given ActiveRecord Field
 *
 * Validation is done with php function preg_match<br />
 * Default error message is <b>%s has invalid format!</b><br />
 * Use Validator::message method to provide you custom error message<br />
 *
 * <code>
 * $this->validates_format_of('email')->with('/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i');
 * $this->validates_format_of('email')->with('/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i')->message('Wrong Format for Email Address!');
 * </code>
 * 
 * @see Validator, ActiveRecord, Field, preg_match
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */
class FormatOfValidator extends Validator {
    
    /** @var string
        default error message */
    protected $message= '%s has invalid format!';
    
    /** @var string
        regex to be used for this validation */ 
    private $with= '';
    
    /**
     * It sets the Regex for this validation
     * 
     * @param string 
     * @return Validator
     */ 
    public function with($regex='') {
        $this->with= $regex;
        return $this;
    }
    
    /**
     * Performs validation
     *
     * If FormatOfValidator::with is not given, or the value of the 
     * Field didnt preg_match the given regex, validation will fail
     * 
     * @see Validator::validates
     */ 
    public function validate(Field $field) {
        if ( $this->with === '' ||  !preg_match($this->with, $field->getValue())) {
            $field->addError(sprintf($this->message, $field->getFormattedName()));
        }
    }

}

/**
 * Validates the presence of a given ActiveRecord Field
 *
 * Validation is done by comparing Field value with empty string<br />
 * Default error message is <b>%s is empty!</b><br />
 * Use Validator::message method to provide you custom error message<br />
 *
 * <code>
 * $this->validates_format_of('name');
 * $this->validates_format_of('name')->message('Did you fill the %s form field?');
 * </code>
 * 
 * @see Validator, ActiveRecord, Field
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */ 
class PresenceOfValidator extends Validator {
    
    /** @var string
        default error message */ 
    protected $message= '%s is empty!';

    /**
     * Performs validation
     * 
     * Validation will fail if the field value equals with PHP empty string ('foo' == '' return FALSE, '0' == '' also returns FALSE)
     * @see Validator::validates
     */ 
    public function validate(Field $field) {
        if ($field->getValue() == '') { 
            $field->addError(sprintf($this->message, $field->getFormattedName()));
        }
    }

}

/**
 * Validates numericality of an ActiveRecord Field
 *
 * Validation is done by using PHP functions is_numeric and invalue<br />
 * Default error message is <b>%s is not a number!</b><br />
 * Use Validator::message method to provide you custom error message<br />
 *
 * <code>
 * $this->validates_numericality_of('price');
 * $this->validates_numericality_of('price')->message('%s should be a number!');
 * </code>
 * 
 * @TODO: test this with float values
 * 
 * @see Validator, ActiveRecord, Field, is_numeric, intvalue
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */ 
class NumericalityOfValidator extends Validator {
    
    /** @var string
        default error message */  
    protected $message= '%s is not a number!';
    
    /**
     * Performs validation
     *
     * @see Validator::validates
     */ 
    public function validate(Field $field) {
        $v=$field->getValue();
        if(!((is_numeric($v)) && (intval(0+$v)==$v))) { 
            $field->addError(sprintf($this->message, $field->getFormattedName()));
        }
    }

}

/**
 * Validates the uniqueness of an ActiveRecord Field
 *
 * Validation is done by performing an sql query that check if the given field with it's value already exists in the database<br />
 * Default error message is <b>%s is not unique!</b><br />
 * Use Validator::message method to provide you custom error message<br />
 *
 * <code>
 * $this->validates_uniqueness_of('username');
 * $this->validates_uniqueness_of('username')->message('%s is already taken!');
 * </code>
 * 
 * @see Validator, ActiveRecord, Field
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */ 
class UniquenessOfValidator extends Validator {
    
    /** @var string
        default error message */ 
    protected $message= '%s is not unique!';

    /**
     * Performs validation
     *
     * The validation fail if this SQL returns a result:
     * <code>
     * SELECT ${FIELD.NAME} FROM ${TABLE} WHERE ${FIELD.NAME}=${FIELD.VALUE} AND ${ROW.PRIMARY_KEY.NAME}!=${ROW.PRIMARY_KEY.VALUE}
     * </code>
     * 
     * @see Validator::validates
     */ 
    public function validate(Field $field) {
        $supp= $this->record->getPrimaryKey()->getValue()===NULL ? '' : ' and ' . $this->record->getPrimaryKey()->getName() . '!=?';
        try {
            ActiveRecord::build(
                new QueryBuilder($this->record->getClassName(), 
                    array(
                        'first', 
                        array('condition'=>$field->getName() . '=?' . $supp,
                              'columns'=>$field->getName()
                    ), 
                    array($field->getValue(), $this->record->getPrimaryKey()->getValue()
                    )
                )
            ));
            $field->addError(sprintf($this->message, $field->getFormattedName()));
        } catch (RecordNotFoundException $rnfEx) {  }
   }
    
}

/**
 * Validates the lenght of an ActiveRecord Field value
 *
 * Validation is done by checking the field <b>length value</b> against accepted minimum / maximum values<br />
 * Default error message if the lenght is too short is <b>%s is to short, minimum is %d!</b><br />
 * Default error message if the lenght is too long is <b>%s is to long, maximum is %d!</b><br />
 * Use Validator::message method to provide you custom error message<br />
 *
 * <code>
 * $this->validates_lenght_of('username')->in(2,5);
 * $this->validates_lenght_of('username')->min(2)->max(5);
 * $this->validates_lenght_of('password')->in(4,10)->too_short('%s needs %d charachters')->too_long('%s is too long');
 * </code>
 * 
 * @see Validator, ActiveRecord, Field
 * @package medick.active.record
 * @subpackage validator
 * @author Oancea Aurelian
 */ 

class LengthOfValidator extends Validator {
    
    /** @var string
        default error message if the lenght of the value is too short */
    protected $too_short= '%s is too short, minimum is %d';

    /** @var string
        default error message if the lenght of the value is too long */ 
    protected $too_long = '%s is too long, maximum is %d';

    /** @var int
        minimum value, defaults to 0 */ 
    protected $min= 0;

    /** @var int 
        maximum value, defaults to 1 */
    protected $max= 1;

    /**
     * It sets a minimum / maximum lenght
     * 
     * @param int mimimum lenght
     * @param int maximum lenght
     * @return LenghtOfValidator
     * @throws MedickException if the given minmum value is grater than the maximum given value
     */ 
    public function in($min, $max) {
        if ( $max <= $min ) { 
            throw new MedickException('Doh, you are too smart!');
        }
        $this->min= (int)$min;
        $this->max= (int)$max;
        return $this;
    }
    
    /**
     * Sets the maximum
     *
     * @param int
     * @return LengthOfValidator
     */ 
    public function max($max) {
        $this->max= $max;
        return $this;
    }

    /**
     * Sets the mimimum
     *
     * @param int
     * @return LenghtOfValidator
     */ 
    public function min($min) {
        $this->min= $min;
        return $this;
    }
    
    /**
     * Sets too short message
     *
     * @param string 
     * @return LenghtOfValidator
     */ 
    public function too_short($message) {
        $this->too_short= $message;
        return $this;
    }
    
    /**
     * Sets too long message
     *
     * @param string
     * @return LenghtOfValidator
     */ 
    public function too_long($message) {
        $this->too_long= $message;
        return $this;
    }

    /**
     * Performs this validatation
     *
     * Validation will fail if the lenght of the Field value is smaller than the mimimum value or grater that the maximum value
     * @see Validator::validates
     */ 
    public function validate(Field $field) {
        $l= strlen($field->getValue());
        if ($l < $this->min) { 
            return $field->addError(sprintf($this->too_short, $field->getFormattedName(), $this->min));
        } elseif ($l > $this->max) { 
            return $field->addError(sprintf($this->too_long, $field->getFormattedName(), $this->max));
        }
    }

}

