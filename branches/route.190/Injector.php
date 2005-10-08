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
 * @package locknet7.action.controller
 */

class Injector extends Object {

    /**
     * Tasks:
     * 1) include the model file
     * 2) investigate the Model class
     * 3) set ActiveRecordBase::$__klass, aka the table name.
     * @TODO: can we hook a Registry here?
     */
    public static function inject($model) {
        $logger = Registry::get('__logger');
        $model_location = Registry::get('__configurator')->getProperty('application_path') . 
            DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
        $logger->debug('Model Location:: ' . $model_location);
        // FIXME: a custom error.
        if (!is_file($model_location)) throw new MedickException ('No such file or directory!');
        
        include_once($model_location);

        $model_name = ucfirst($model);
        
        $logger->debug('Model Name:: ' .$model_name);
        
        $model_object = new ReflectionClass($model_name);

        if ($model_object->getParentClass()->name != 'ActiveRecordBase') {
            throw new MedickException ('Wrong Definition of your Model, ' . $model_name . ' must extend ActiveRecordBase object!');
        }
        $logger->debug('Table:: ' .$model);
        ActiveRecordBase::setTable($model);
    }

    /** prepare static members for our model */
    public static function prepareARBase() {
        return ActiveRecordBase::establish_connection();
    }
}

