<?php
// $Id$

// abstract hooks
include_once('active/record/drivers/abstract/SQLConnection.php');
include_once('active/record/drivers/abstract/SQLPreparedStatement.php');
include_once('active/record/drivers/abstract/SQLResultSet.php');
include_once('active/record/drivers/abstract/SQLTableInfo.php');

class MocTableInfo extends SQLTableInfo {

}

class MockConnection extends SQLConnection {
  
  public function connect(Array $dsn=array()) {
    return true;
  }
  
  public function close() {
    return true;
  }
  
  public function nextId() {
    
  }
  
  public function getLastErrorMessage() {
    return 'mock.';
  }

  public function getTableInfo($name, $force=false) {
    return new MockTableInfo($name, $this);
  }

  public function execute( $sql ) {

  }

  public function exec( $sql ) {

  }

  public function getUpdateCount($rs=null) {

  }

  public function prepare( $sql ) {
    return new MockPreparedStatement($sql, $this);
  }
  
}
