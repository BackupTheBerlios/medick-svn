<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
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
 * @package medick.action.view
 * @subpackage helpers
 * @author Oancea Aurelian
 */
class ActiveRecordHelper extends Object {

    /**
     * Finds and retuns a HTML formatted string with errors for an ActiveRecord object
     *
     * Eg.
     * <code>
     *  <?=ActiveRecordHelper::error_messages_for($person);?>
     * </code>
     * will show all the errors for the person object
     * @param ActiveRecord object the ActiveRecord object to check for errors
     * @param array options the options where we can cusomize the look and feel of the error message.
     *                      this includes: css_class and heading
     * @return string a HTML formatted string
     */
    public static function error_messages_for(ActiveRecord $object, $options=array()) {
        $css_class= isset($options['css_class']) ? $options['css_class'] : 'formErrors';
        $heading  = isset($options['heading']) && (int)$options['heading'] > 0 && (int)$options['heading'] < 6 ? $options['heading'] : 2;
        $buffer= '<div id="medickFormErrors" class="' . $css_class . '">';
        $errors= 0;
        $part = '';
        $it= $object->getRow()->iterator();
        while ($it->hasNext()) {
            $current= $it->next();
            if ($current->hasErrors()) {
                $part .= '<ul>';
                foreach ($current->getErrors() as $error) {
                    $part .= '<li>' . ucfirst($current->getName()) .' ' . $error . '</li>';
                    $errors++;
                }
                $part .= '</ul>';
            }
        }
        if ($errors > 0) {
            $st= $errors == 1 ? 'error' : 'errors';
            $buffer .= '<h'.$heading.'>'.$errors.' '.$st.' prohibited this ';
            $buffer .= ucfirst(get_class($object)).' from being saved</h'.$heading.'>';
            $buffer .= "\n<p>There were problems with the following fields:</p>\n";
            $buffer .= $part;
            return $buffer . '</div>';
        }
    }

    public static function error_message_on(ActiveRecord $object, $method, $options=array()) {
        // $prepend_text = isset($options['prepend']) ? $options['prepend'] : "";
        // $append_text  = isset($options['append']) ? $options['append'] : "";
        // $css_class    = isset($options['css_class']) ? $options['css_class'] : "formError";
        $field= $object->getRow()->getFieldByName($method);
        if (!$field or !$field->hasErrors()) {
            return;
        }
        $buff= '<div class="formErrors">';
        foreach ($field->getErrors() as $error) {
            $buff .= $error . '<br />';
        }
        $buff .= "</div>\n";
        return $buff;
    }

}

/**
 *
 * @package medick.action.view
 * @subpackage helpers
 * @see http://api.rubyonrails.com/classes/ActionView/Helpers/FormHelper.html
 * @author Oancea Aurelian
 */
class FormHelper extends Object {

    public static function text_field(ActiveRecord $object, $method, $options = array()) {
        if (!$field= $object->getRow()->getFieldByName($method)) {
            return; // ex?
        }
        $id   = strtolower(get_class($object)) . '_'.$method;
        $name = strtolower(get_class($object)).'['.$method.']';
        $buff = '';
        $errors= FALSE;
        if($field->hasErrors()) {
            $buff .= '<div class="fieldWithErrors">';
            $errors= TRUE;
        }

        $buff .= '<input type="text" id="' . $id . '" ';
        $buff .= 'name="'.$name . '" ';
        $buff .= 'value="'.$object->$method.'" ';
        foreach ($options as $key=>$value) {
            $buff .= $key . '="'.$value.'" ';
        }
        $buff .= ' />';
        if ($errors) {
            $buff .= '</div>';
        }
        return $buff;
    }

    public static function text_area(ActiveRecord $object, $method, $options=array()) {
        if (!$field= $object->getRow()->getFieldByName($method)) {
            return; // ex?
        }
        $id   = strtolower(get_class($object)) . '_'.$method;
        $name = strtolower(get_class($object)).'['.$method.']';
        $buff = '';
        $errors= FALSE;
        if($field->hasErrors()) {
            $buff .= '<div class="fieldWithErrors">';
            $errors= TRUE;
        }
        $buff .= '<textarea id="' . $id.'" name="'.$name .'"';
        foreach ($options as $key=>$value) {
            $buff .= $key . '="'.$value.'" ';
        }
        $buff .= '>';
        $buff .= $object->$method;
        $buff .= '</textarea>';
        if ($errors) {
            $buff .= '</div>';
        }
        return $buff;
    }

    public static function check_box(Object $object, $method, $options = array()) {
        if (!$field= $object->getRow()->getFieldByName($method)) {
            return; // ex?
        }
        $id   = strtolower($object->getClassName()) . '_' .$method;
        $name = strtolower($object->getClassName()) . '[' . $method . ']';
        $buff = '';
        $errors= FALSE;
        if($field->hasErrors()) {
            $buff .= '<div class="fieldWithErrors">';
            $errors= TRUE;
        }
        
        $buff .= '<input type="checkbox" id="' . $id . '" name="' . $name . '" ';
        
        foreach ($options as $key=>$value) {
            $buff .= $key . '="' . $value . '" ';
        }

        $value= $object->$method === NULL ? 0: $object->$method;
        
        if ( (int)$value > 0 ) {
            $buff .= 'checked="checked"';// value="' . $value . '"';
        } else {
            // $buff .= 'value="0"';
        }
        $buff .= ' />';
        if ($errors) {
            $buff .= '</div>';
        }
        return $buff;
        // . '<input name="' . $name . '" value="' . $value . '" type="hidden" />';
        // return $buff;
    }
}

/**
 *
 * @package medick.action.view
 * @subpackage helpers
 * @author Oancea Aurelian
 */
class URL extends Object {

    public static function create($controller, $action='index', $params=array(), $ext='html') {
        $config = Registry::get('__configurator');
        $base   = (string)$config->getWebContext()->document_root;
        $rewrite= (string)strtolower($config->getWebContext()->rewrite);
        if ($rewrite == 'false' or $rewrite == 'off' or $rewrite == '0') {
            $base .= '/index.php';
        }
        $buff = $base . '/' . $controller . '/' . $action;
        foreach ($params as $key=>$value) {
            $buff .= '/' . $value;
        }
        return $buff . '.' . $ext;
    }
}

/**
 *
 * @package medick.action.view
 * @subpackage helpers
 * @deprecated use FormHelper since it provides more features
 * @author Oancea Aurelian
 */
class Form extends Object {

    public function __construct($action, $method) {  }

    public static function submit($name, $value = 'Submit', $attr = '') {
        $buff = '<input type="submit" name="' . $name . '" value="' . $value . '" ';
        return $buff . self::parseAttributes($attr) . ' />';
    }

    public static function text($name, $value = null, $attr = '') {
        $buff  = '<input type="text" name="' . $name . '" ';
        if (!is_null($value)) $buff .= 'value="' . $value . '" ';
        return $buff . self::parseAttributes($attr) . ' />';
    }

    public static function hidden($name, $value) {
        return '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
    }

    public static function textarea($name, $value = null, $attr='') {
        $buff  = '<textarea name="' . $name . '"';
        if ($attr!='') {
            $buff .= self::parseAttributes($attr);
        }
        $buff .= '>';
        if (!is_null($value)) $buff .= $value;
        return $buff . '</textarea>';
    }

    public static function checkbox($name, $checked = false, $attr = '') {
        $buff = '<input type="checkbox" name="' . $name . '" ';
        if ($checked && $checked !== 'off') $buff .= ' checked="checked"';
        return $buff . self::parseAttributes($attr) . ' />';
    }

    private static function parseAttributes($attr) {
        $buff = '';
        if (is_array($attr)) {
            foreach ($attr AS $atribute=>$val) {
                $buff .= $atribute . '="' . $val . '" ';
            }
        } elseif($attr != '') {
            $buff .= $attr;
        }
        return $buff;
    }
}

