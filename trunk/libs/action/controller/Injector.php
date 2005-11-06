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
 * Model Injector.
 * Injects the model name into Active Record Base Class.
 * @TODO: this should be used for other types of injections. (controller, layout files, etc.)
 * @package locknet7.action.controller
 */

class Injector extends Object {

    /**
     * Tasks:
     * 1) include the model file
     * 2) investigate the Model class
     * @throws InjectorException
     */
    public static function inject($model) {
        $model_location = Registry::get('__configurator')->getProperty('application_path') .
            DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
         
        $logger = Registry::get('__logger');
        $logger->debug('Model Location:: ' . $model_location);
        $logger->debug('Model Name:: ' . ucfirst($model));
        if(!@file_exists($model_location) ) {
            throw new FileNotFoundException('Cannot load your model: `' . $model .'`, no such file in: `' . $model_location . '`');
        } else {
            include_once($model_location);
        }
          
        $model_object = new ReflectionClass(ucfirst($model));

        if (@$model_object->getParentClass()->name != 'ActiveRecordBase') {
            throw new InjectorException ('Wrong Definition of your Model, `' . ucfirst($model) . '` must extend ActiveRecordBase object!');
        }
        // if (!$model_object->hasMethod('find')) { XXX. php 5.1 only.
        try {
            $method= $model_object->getMethod('find');
            if (!$method->isStatic() && !$method->isPublic()) {
                throw new InjectorException('Class method: ' . ucfirst($model) . '::find() should be declared static and public!');
            }
        } catch (ReflectionException $rex) {
            throw new InjectorException (
                'Cannot Inject your Model, `' . ucfirst($model) . '`!
                The dummy `find` method is not defined! [ User Info: ' . $rex->getMessage() . ']');
        }
    }
}

