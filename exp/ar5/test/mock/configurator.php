<?php
// $Id$

include_once('context/configurator/IConfigurator.php');

class MockConfigurator extends Object implements IConfigurator {  
  
  function getLoggerOutputters() { return array(); }
  
  function getLoggerFormatter() { return ''; }
  
  function getDatabaseDsn($id=false) {
    return array('phptype'=>'mock');
  }
  
}
