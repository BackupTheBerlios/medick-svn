<?php
// {{{ HEADER
/********************************************************************************************************************
 * $Id$
 *
 * Copyright (c) 2005, Oancea Aurelian
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 *******************************************************************************************************************/
// }}}
/** 
 * Model Injector.
 * Injects the model name into Active Record Base Class.
 * @package locknet7.action.controller.dependency 
 */

class ModelInjector {

    /**
     * Tasks:
     * 1) include the model file
     * 2) investigate the Model class
     * 3) set ActiveRecordBase::$__klass
     */
    public static function inject($model) {
        $logger = Logger::getInstance();
        $model_location = TOP_LOCATION . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
        $logger->debug('Model Location:: ' . $model_location);
        // FIXME: a custom error.
        if (!is_file($model_location)) throw new Exception ('No such file or directory!');
        
        include_once($model_location);

        $model_name = ucfirst($model);
        
        $logger->debug('Model Name:: ' .$model_name);
        
        $model_object = new ReflectionClass($model_name);

        if ($model_object->getParentClass()->name != 'ActiveRecordBase')
            throw new Exception ('Wrong Definition of your Model, he must extend ActiveRecordBase object!');

        ActiveRecordBase::setTable($model);
        
    }

}
