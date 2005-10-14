<?php

// $Id$
    
class MockConfigurator extends Object {

    public function getLoggerOutputters() {
        return array();    
    }

    public function getLoggerFormatter() {
        return FALSE;
    }

    public function getDatabaseDsn() {
        return array(
             'phptype'  => 'sqlite',
             'database' => 'test.db');
    }

}

?>
