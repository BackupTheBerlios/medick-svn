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


class ActiveRecordHelper extends Object {

    public static function error_messages_for(ActiveRecordBase $object) {
        $r= '<div id="form_errors">';
        $errors= 0;
        $p = '';
        $it= $object->getRow()->iterator();
        while ($it->hasNext()) {
            $current= $it->next();
            if ($current->hasErrors()) {
                $p .= '<ul>';
                foreach ($current->getErrors() as $error) {
                    $p .= '<li>' . ucfirst($current->getName()) .' ' . $error . '</li>';
                    $errors++;
                }
                $p .= '</ul>';
                // $r[$current->getName()]= $current->getErrors();
            }
        }
        if ($errors > 0) {
            $r .= '<h2>' . $errors . ' errors prohibited this ' . ucfirst(get_class($object)) . ' from being saved</h2>';
            $r .= '<p>There were problems with the following fields:</p>';
            $r .= $p;
            return $r . '</div>';
        }
    }

}

/** @see http://api.rubyonrails.com/classes/ActionView/Helpers/FormHelper.html */

class FormHelper extends Object {

    public static function text_field(Object $object, $method, $options = array()) {
        $buff = '<input type="text" id="' . strtolower(get_class($object)) . '_'.$method.'" ';
        $buff .= 'name="'.strtolower(get_class($object)).'['.$method.']" ';
        $buff .= 'value="'.$object->$method.'" ';
        foreach ($options as $key=>$value) {
            $buff .= $key . '="'.$value.'" ';
        }
        return $buff . ' />';
    }

    public static function text_area(Object $object, $method, $options=array()) {
        $buff = '<textarea ';
        $buff .= 'id="' . strtolower(get_class($object)) . '_'.$method.'" ';
        $buff .= 'name="'.strtolower(get_class($object)).'['.$method.']" ';
        foreach ($options as $key=>$value) {
            $buff .= $key . '="'.$value.'" ';
        }
        $buff .= '>';
        $buff .= $object->$method;
        $buff .= '</textarea>';
        return $buff;
    }

    public static function check_box(
            Object $object,
            $method,
            $options = array(),
            $checked_value = "1",
            $unchecked_value = "0") {
        $buff = '<input type="checkbox" id="';
        $buff .= strtolower(get_class($object)) . '_'.$method.'" ';
        $buff .= 'name="'.strtolower(get_class($object)).'['.$method.']" ';
        foreach ($options as $key=>$value) {
            $buff .= $key . '="'.$value.'" ';
        }
        if ((int)$object->$method > 0) {
            $buff .= 'value="1" checked="checked" ';
        } else {
            $buff .= 'value="0"';
        }

        $buff .= ' />';
        // $buff .= '<input name="'.strtolower(get_class($object)).'['.$method.']" type="hidden" ';
        return $buff;
    }
}

/**
 * This package will be deprecated at one point and replaced with a modern view.
 * @package locknet7.action.view.HTML
 */
class HTMLElement extends Object {
    public function __construct() {     }
}

class URL extends Object {
    public static function create($controller, $action='index', $params=array(), $separator='&amp;') {
        $config = Registry::get('__configurator');
        $base   = $config->getProperty('document_root');
        if (!$config->getProperty('rewrite')) {
            $buff = $base . '/index.php?controller=' . $controller . $separator . 'action=' . $action;
            foreach ($params AS $key=>$value) {
                $buff .= $separator . $key . '=' . $value;
            }
            return $buff;
        } else {
            $buff = $base . '/' . $controller . '/' . $action;
            foreach ($params AS $key=>$value) {
                $buff .= '/' . $value;
            }
            return $buff . '.html';
        }
    }
}

/** @deprecated USE FormHelper! */
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

