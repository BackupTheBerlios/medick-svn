<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of locknet.ro nor the names of its contributors may
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

include_once('active/record/Base.php');

/**
 * Solves dependencys, by including application specific files
 * controllers, models, layout files.
 * Aditional using php reflection api, it validates the user classes
 * @package locknet7.action.controller
 */

class Injector extends Object {

    /** @var string
        holds various application paths based on what we have defined in the configuration file. */
    private $path= array();

    /** @var Logger
        A logger instance */
    private $logger;

    /** @var IConfigurator
        A Configurator instance */
    private $config;

    /**
     * Creates a new instance of this Injector.
     */
    public function __construct() {
        $this->config = Registry::get('__configurator');
        $this->logger = Registry::get('__logger');
        $app_path = $this->config->getProperty('application_path') . DIRECTORY_SEPARATOR;
        $app_path .= 'app' . DIRECTORY_SEPARATOR;
        $this->path['__base']      = $app_path;
        $this->path['models']      = $app_path . 'models'      . DIRECTORY_SEPARATOR;
        $this->path['controllers'] = $app_path . 'controllers' . DIRECTORY_SEPARATOR;
        $this->path['views']       = $app_path . 'views'       . DIRECTORY_SEPARATOR;
        $this->path['layouts']     = $this->path['views']      . 'layouts' . DIRECTORY_SEPARATOR;
        $this->path['helpers']     = $app_path . 'helpers'     . DIRECTORY_SEPARATOR;
        $this->inject('include_path');
    }

    /**
     * Wrapper method for inject* methods.
     * @param string type, the type of the injector.
     * @param mixed param, additional parameters to pass to the injector.
     * @throws InjectorException
     */
    public function inject($type, $param='') {
        $types= array('model', 'controller', 'helper', 'layout', 'include_path');
        $method= 'inject' . ucfirst($type);
        if (!method_exists($this, $method)) {
            throw new InjectorException('Call to undefined method: `' . $this->getClassName() . '->' . $method . '(string ' . $param . ')`');
        } elseif (in_array($type, $types)) {
            if ($param=='') return $this->$method();
            else return $this->$method($param);
        } else {
            throw new InjectorException('Unknow injection type: `' . $type . '`');
        }
    }

    /**
     * It gets the path.
     * @param string path type
     * @return string
     */
    public function getPath($type) {
        return isset($this->path[$type]) ? $this->path[$type] : $this->path;
    }


    /**
     * Adds user folders, `vendor' and `libs` to the include_path
     */
    public function injectInclude_path() {
        $top= $this->path['__base'] . '..' . DIRECTORY_SEPARATOR;
        if (is_dir($top . 'vendor')) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $top . 'vendor');
        }
        if (is_dir($top . 'libs')) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $top . 'lib');
        }
    }

    /**
     * Injects a user model
     *
     * @param string name, the model name
     * @throws FileNotFoundException if the model file is not found.
     * @throws InjectorException if the model class definition is wrong
     * @return void
     * @access protected
     */
    protected function injectModel($name) {
        $location= $this->path['models'] . $name . '.php';
        $this->logger->debug('Model Location:: ' . $location);
        $this->logger->debug('Model Name:: ' . ucfirst($name));

        $this->includeFile($location, ucfirst($name));

        try {
            $model_object = new ReflectionClass(ucfirst($name));

            if (@$model_object->getParentClass()->name != 'ActiveRecordBase') {
                throw new InjectorException (
                    'Wrong Definition of user Model, `' . ucfirst($name) . '`, it must extend ActiveRecordBase object!');
            }

            // if (!$model_object->hasMethod('find')) { XXX. php 5.1 only.

            $method= $model_object->getMethod('find');
            if (!$method->isStatic() && !$method->isPublic()) {
                throw new InjectorException (
                    'Class method: ' . ucfirst($name) . '::find([mixed arguments]) should be declared as static and public!');
            }
        } catch (ReflectionException $rEx) {
            throw new InjectorException (
                'Cannot Inject user Model, `' . ucfirst($name) . '`!
                The dummy `find` method is not defined! [ User Info: ' . $rEx->getMessage() . ']');
        }

    }

    /**
     * Injects the User Controller
     *
     * @param string name, the name of this conroller
     * @return ActionControllerBase
     * @throws FileNotFoundException if the controller class file cannot be loaded.
     * @throws InjectorException if the controller definition is malformated.
     * @access protected
     */
    protected function injectController($name) {
        try {
            $this->includeFile($this->path['controllers'] . 'application.php', 'ApplicationController');
        } catch (FileNotFoundException $fnfEx) {
            $this->logger->warn($fnfEx->getMessage);
        }

        $file= $this->path['controllers'] . strtolower($name) . '_controller.php';
        $clazz= ucfirst($name)    . 'Controller';

        $this->includeFile($file, $clazz);

        try {
            $controller_class = new ReflectionClass($clazz);
            if (
                ($controller_class->isInstantiable())
                &&
                (
                    (@$controller_class->getParentClass()->name == 'ApplicationController')
                    ||
                    (@$controller_class->getParentClass()->name == 'ActionControllerBase')
                )
            )
            {
                return $controller_class->newInstance();
            } else {
                throw new InjectorException (
                    'Wrong Definition of user controller class,
                    `' . $clazz . '` must extend ApplicationController or ActionControllerBase and
                    should be instantiable (must have a public constructor)!');
            }
        } catch (ReflectionException $rEx) {
            throw new InjectorException ('Cannot inject user controller class `' . $clazz . '` [ User Info ] :' . $rEx->getMessage());
        }
    }

    /**
     * Injects a user helper.
     *
     * @param string location, base location of this helper.
     * @throws FileNotFoundException if the helper cannot be loaded.
     * @return void
     * @access private
     */
    protected function injectHelper($location) {
        $helper_file= $this->path['helpers'] . $location . '_helper.php';
        $this->logger->debug('Trying to load user helper from: ' . $helper_file);
        return $this->includeFile($helper_file, $location . '_helper.php');
    }

    /**
     * Helper to include a user file
     *
     * @param string location, the location of the file to be included.
     * @param string name, the name withc will be used in the exception thrown.
     * @throws FileNotFoundException if the file cannot be loaded.
     * @access private
     */
    private function includeFile($location, $failure_message) {
        if(!@file_exists($location) ) {
            throw new FileNotFoundException('Cannot load : `' . $failure_message .'`. Searched in: `' . $location . '`');
        } else {
            include_once($location);
        }
    }
}
