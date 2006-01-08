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
 * @package locknet7.action.view.base
 */

include_once('action/view/HTML.php');

// namespace ActionView {

interface ITemplateEngine {
    public function render($template_file);
    public function assign($name, $value);
}

class ActionViewBase extends Object {

    public static function factory($engine) {
        return new PHPTemplateEngine();
    }

    /**
     * Strips slashes
     * This method is called recursive
     * TODO: Move this OUT of this class, or, in __set.
     * TODO: What if $value is Object?
     * @param mixed value, the value on witch we strip slashes.
     *                  It can be array/string or object.
     */
    public static function stripslashes_deep($value) {
        if (is_array($value)) {
            array_map(array('ActionViewBase','stripslashes_deep'), $value);
        } elseif (is_object($value)) {

        } else {
            stripslashes($value);
        }
        return $value;
     }
}

/**
 * ActionViewBase is the default `Template Engine' for Medick Framwork.
 *
 * For a smoother transaction from <tt>Smarty</tt>, some variabiles/methods
 * may share the same name and behavior
 */
class PHPTemplateEngine extends Object implements ITemplateEngine {

    private $vars= array();

    public function render_file($template_file) {
        return $this->render($template_file);
    }

    /**
     * Render the file
     * @param string, file, the file to render.
     * @return string, contents of the file
     * @throws Exception if the file is wrong.
     */
    public function render($file) {
        if (!is_file($file)) throw new MedickException ('Cannot Find Template: ' . $file);
        if (!empty($this->vars)) {
            if(!get_magic_quotes_gpc()) $this->vars = ActionViewBase::stripslashes_deep($this->vars);
            extract($this->vars,EXTR_SKIP);
        }
        ob_start();
        include_once($file);
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }

    /**
     * Wrapper for __set
     * A convenient way to make the migration from Smarty smoother
     * @param name
     * @param value
     */
    public function assign($name, $value){
        $this->__set($name, $value);
    }

    /**
     * Overload default set behavior from PHP
     * This way, I`m able to register the vars passed to the template
     * Advantages: cannot call from template the private vars of this class
     * since $vars will act as a registry system
     * @param name, mixed, the name of the var.
     * @param value, mixed, the value of var.
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Overload the default behavior of PHP get var.
     * We will rise an error if the var is not set
     * @param name, string, the name of var.
     */
    public function __get($name) {
        return array_key_exists($name,$this->vars) ?
                 $this->vars[$name] : trigger_error("Undefined Template Variable: " . $name, E_USER_ERROR);
    }

}

// }
