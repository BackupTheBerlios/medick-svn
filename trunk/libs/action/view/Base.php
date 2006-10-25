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

include_once('action/view/HTML.php');

/**
 *
 * @package medick.action.view
 * @author Oancea Aurelian
 */
interface ITemplateEngine {
    public function render_partial($controller, $partial);
    public function render($template_file);
    public function assign($name, $value);
}

/**
 *
 * @package medick.action.view
 * @author Oancea Aurelian
 */
class ActionView extends Object {

    public static function factory($engine) {
        $clazz= strtoupper($engine) . 'TemplateEngine';
        return new $clazz;
    }

    /**
     * Strips slashes, method called recursive
     *
     * @todo Move this OUT of this class, or, in __set.
     * @todo What if $value is Object?
     * @param mixed value, the value on witch we strip slashes.
     *                  It can be array/string or object.
     */
     
    /* public static function stripslashes_deep($value) {
        if (is_array($value)) {
            array_map(array('ActionView','stripslashes_deep'), $value);
        } elseif (is_object($value)) {

        } else {
            stripslashes($value);
        }
        return $value;
     } */ 
}

/**
 * PHPTemplateEngine is the default `Template Engine' for Medick Framwork.
 *
 * For a smoother transaction from Smarty, some variabiles/methods
 * may share the same name and behavior
 *
 * @package medick.action.view
 * @author Oancea Aurelian
 */
class PHPTemplateEngine extends Object implements ITemplateEngine {

    /** @var array
        Container for templates variables */
    private $vars= array();

    /**
     * It renders a template file
     *
     * @param string template file
     */
    public function render_file($template_file) {
        return $this->render($template_file);
    }

    /**
     * It renders a partial template.
     *
     * This method can be called from the templates. Usualy it is used with AJAX calls
     *
     * @param string controller
     * @param string patrial
     */
    public function render_partial($controller, $partial) {
        $path= Registry::get('__injector')->getPath('views');
        if (NULL === $controller) {
            $path .= $this->__controller;
        } else {
            $path .= $controller;
        }
        $path .= DIRECTORY_SEPARATOR . '_' . $partial . '.phtml';
        return $this->render($path);
    }

    /**
     * Render the file.
     *
     * @param string file, the file to render.
     * @return string contents of the file
     * @throws FileNotFoundException if we cannot find the specified file
     */
    public function render($file) {
        if (!is_file($file)) {
            throw new FileNotFoundException ('Cannot Find Template: ' . $file);
        }
        if (!empty($this->vars)) {
            // if(!get_magic_quotes_gpc()) $this->vars = ActionView::stripslashes_deep($this->vars);
            extract($this->vars,EXTR_SKIP);
        }
        ob_start();
        include_once($file);
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }

    /**
     * Wrapper for __set.
     *
     * A convenient way to make the migration from Smarty smoother
     * @see PHPTemplateEngine::__set()
     * @param string name
     * @param string value
     */
    public function assign($name, $value){
        $this->__set($name, $value);
    }

    /**
     * Overload default __set behavior from PHP
     *
     * This way, I`m able to register the vars passed to the template.
     *
     * Advantages: cannot call from template the private vars of this class
     * since $vars will act as a registry system
     * @param string name the name of the variable
     * @param mixed value the value of variable
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Overload the default behavior of PHP get var.
     *
     * NOTE: Will rise an error if the variable is not set
     * @param string name the name of variable
     */
    public function __get($name) {
        return array_key_exists($name,$this->vars) ?
                 $this->vars[$name] : trigger_error("Undefined Template Variable: " . $name, E_USER_ERROR);
    }
    
    public function dump($name='') {
      echo '<div style="color:#000;background-color:#FFF;"><pre>';var_dump($name==''?$this->vars:$this->vars[$name]);echo '</pre></div>';
    }
    
    public function defined_vars() {
      echo '<div style="color:#000;background-color:#FFF;"><pre>';
      foreach ($this->vars as $name=>$value) {
        echo $name . "\n";
      }
      echo '</pre></div>';
    }

}

