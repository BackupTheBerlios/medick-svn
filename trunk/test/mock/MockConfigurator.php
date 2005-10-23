<?php

// $Id$
    
include_once('configurator/IConfigurator.php');
 
class MockConfigurator extends Object implements IConfigurator {
 
    public function getLoggerOutputters() {
        return array();
    }
  
    public function getLoggerFormatter() {
        return FALSE;
    }
  
    public function getProperty($name) {
        switch ($name) {
            case 'application_path':
                return 'application' . DIRECTORY_SEPARATOR;
            default:
                throw new ConfiguratorException(__CLASS__ . ' Property `' . $name . '` not implemented!');
         }
     }
 
     public function getDatabaseDsn($id=FALSE) {
         return array(
               'phptype'  => 'sqlite',
               'database' => TMP . 'test.db');
    }
}
