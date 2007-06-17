<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Oancea Aurelian < aurelian [ at ] locknet [ dot ] ro >
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
 * Class for reading console options.
 *
 * <code>
 *  // create a new ConsoleOptions Object, loaded with $argv
 *  $c= new ConsoleOptions(isset($argv)?$argv:$_SERVER['argv']); 
 *  $c->alias('controller', '-c, --c'); // sets some aliases.
 *  $c->alias('methods', '-m, --m, --methods');
 *  // script runned with php script.php -c invoker --methods "index, foo"
 *  $c->has('controller'); // TRUE
 *  $c->get(); // invoker
 *  $c->has('-c'); // TRUE
 *  $c->get(); // invoker
 *  $c->get('methods'); // index, foo
 *  $c->get('foo'); // NULL
 * </code>
 * 
 * @see http://www.php.net/manual/en/features.commandline.php
 * @package medick.core
 * @subpackage console
 * @author Aurelian Oancea
 */
class ConsoleOptions extends Object {
    /** @var array
        Container for console Options */
    private $options;
    /** @var string
        internal index */
    private $index;
    /** @var bool
        true if the options are loaded */
    private $isLoaded;
    /** @var string
        the current script name */
    private $scriptName;
    /** @var array
        a list of defined aliases */
    private $aliases;

    private $skipValues;
    
    /**
     * Creates a new ConsoleOptions object
     *
     * @param array options, the initial set of unparsed options
     */
    public function ConsoleOptions($options= array()) {
        $this->isLoaded   = false;
        $this->options    = array();
        $this->skipValues = array();
        $this->aliases    = array();
        if (!empty($options)) {
            $this->load($options);
        }
        $this->index      = NULL;
    }
    
    /**
     * Loads options
     *
     * Note: First option passed is discarded from the options list.
     *       We assume that this is the script name
     *
     * We pass an array like:
     * <code>
     *   array(__SCRIPT__NAME__, param1 param2 param3 param4 param5 "param6 param7");
     * </code>
     * And we build this set of options:
     * <code>
     *   array('param1'=>"param2",
     *         'param3'=>"param4",
     *         'param5'=>"param6 param7"
     *         )
     * </code>
     * @param array options
     * @throws IllegalStateException if it is already loaded.
     */
    public function load($options) {
        if ($this->isLoaded) {
            throw new IllegalStateException('Options already loaded!');
        }
        $this->scriptName= array_shift($options);
        $i=0;
        while($i<count($options)) {
            if (in_array($options[$i], $this->skipValues)) {
                $this->options[$options[$i]]= -1;
                $i++;
            } else {
                $this->options[$options[$i]]= isset($options[$i+1]) ? $options[$i+1] : NULL;
                $i=$i+2;
            }
        } 
        $this->isLoaded= true;
    }
    
    /**
     * Adds an empty value for a list of options
     *
     * Options are taken with func_get_args() and a second call to this method will
     * overwrite the values previously added.
     *
     * Add aliases too!
     *
     * <code>
     *  // script called with php script.php --force --controller invoker
     *  $c= new ConsoleOptions();
     *  $c->setNoValueFor('debug','force','-f','--force','-d','--debug'); // before loading!
     *  $c->load($args);
     *  $c->alias('force', '-f, --force'); // after loading
     *  $c->alias('debug', '-d, --debug'); // after loading
     *  $c->has('force'); // returns true
     *  $c->get(); // returns -1
     *  $c->has('controller'); // returns true
     *  $c->get(); // return invoker
     * </code>
     * @throws IllegalStateException if the options are loaded
     */ 
    public function setNoValueFor() {
        if ($this->isLoaded) {
            throw new IllegalStateException('Options already loaded, cannot add empty values!');
        }
        $this->skipValues= func_get_args();
    }
    
    /**
     * Check if it has option
     *
     * @param string option the option to check for
     * @return bool TRUE if it has.
     * @throws IllegalStateException if this object not loaded
     */
    public function has($option) {
        if (!$this->isLoaded) {
            throw new IllegalStateException('Options should be loaded first!');
        }
        if ( array_key_exists($option, $this->options) && !is_null($this->options[$option]) ) {
          $this->index= $option;
          return true;
        } 
        return false;
    }
    
    /**
     * It gets the option
     *
     * If the index is not provided, we assume that a we 
     * did a previous call to CollectionOptions::has() method
     * @see ConsoleOptions::has(option)
     * @param string index, default null
     * @return string or null if the option is missing
     * @throws IllegalStateException
     */
    public function get($index= NULL) {
        if (!$this->isLoaded) {
            throw new IllegalStateException('Options should be loaded first!');
        }
        if (is_null($index) && !is_null($this->index) ) {
            return $this->options[$this->index];
        } elseif ($this->has($index)) {
            return $this->get();
        } else {
            return NULL;
        }
    }
    
    /**
     * It sets a console alias.
     *
     * @param string for
     * @param mixed aliases, use a list of values separated by comma 
     *                       to provide multiple aliases or an array
     * @throws IllegalStateException
     */
    public function alias($for, $aliases) {
        if (!$this->isLoaded) {
            throw new IllegalStateException('Options should be loaded first!');
        }
        if (is_string($aliases)) {
            $this->aliases[$for]= explode(', ', trim($aliases));
        }
        $this->aliases[$for][]=$for;
        foreach ($this->aliases[$for] as $value) {
            if ($this->has(trim($value))) {
                $this->loadAliases(trim($value), $this->aliases[$for]);
                break;
            }
        }
    }
    
    /**
     * It gets a list of aliases set for $for
     *
     * @param string for aliases to get
     * @return array or null if we didnt define any aliasses for this entry
     */
    public function getAliasesFor($for) {
        return isset($this->aliases[$for])?$this->aliases[$for]:null;
    }
    
    /**
     * It gets the current script name
     *
     * @return string the script name
     */
    public function getScriptName() {
        return $this->scriptName;
    }
    
    /**
     * It gets the current set of options
     *
     * @return array options
     */ 
    public function getOptions() {
        return $this->options;
    }
    
    /**
     * Internal Helper, sets the value of aliases 
     * to the same value as main option
     *
     * @param string value we found this one
     * @param array options
     */
    private function loadAliases($value, $options) {
        unset($options[$value]);
        foreach ($options as $option) {
            $this->options[trim($option)]= $this->options[$value];
        }
    }
}

