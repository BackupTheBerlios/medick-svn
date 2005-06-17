<?php

/** @package locknet7.dependency */

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
