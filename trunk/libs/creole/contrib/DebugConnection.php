<?php
  /**
   * Debug implementation of Connection.
   *
   * This is a Connection that implements the decorator pattern, wrapping around
   * the true Connection object (stored in $childConnection). This Connection
   * tracks information about queries executed and makes that information available
   * for debugging purposes. The information tracked is the last query executed
   * on the connection (getLastExecutedQuery()) and the total number of
   * queries executed on the connection thus far (getNumQueriesExecuted()).
   *
   * To use this debug connection, you need to register it as a new Creole
   * driver that handles all connection types. To do this, call the following
   * before calling Creole::getConnection():
   *
   * <code>
   * Creole::registerDriver('*', 'creole.drivers.debug.DebugConnection');
   * </code>
   *
   * The next call to Creole::getConnection() will return an instance of
   * DebugConnection.
   * 
   * @author Michael Sims
   * @package creole.drivers.debug
   */
  class DebugConnection implements Connection {
    /** @var Connection */
     private $childConnection = null;
     /** @var int */
     private $numQueriesExecuted = 0;
     /** @var string */
     private $lastExecutedQuery = '';
     /**
      * @var object Instance of PEAR Log (or other class with log() method).*/
     private $logger;
     /** 
      * Sets a Logger class (e.g. PEAR Log) to use for logging.
      * The logger class must have a log() method.  All messages are logged at default log level.
      * @param object $logger
      */
     public function setLogger($logger)
     {
       $this->logger = $logger;
     }
     /**
      * Returns the number of queries executed on this connection so far
      *
      * @return int
      */
     public function getNumQueriesExecuted()
     {
       return $this->numQueriesExecuted;
     }
     /**
      * Returns the last query executed on this connection
      *
      * @return string
      */
     public function getLastExecutedQuery()
     {
       return $this->lastExecutedQuery;
     }
     
     /**
      * connect()
      */
      public function connect($dsninfo, $flags = 0)
      {
        if (!($driver = Creole::getDriver($dsninfo['phptype']))) {
          throw new SQLException("No driver has been registered to handle connection type: $type");
        }		
        $connectionClass = Creole::import($driver);
        $this->childConnection = new $connectionClass();
        $this->log("connect(): DSN: ". var_export($dsninfo, true) . ", FLAGS: " . var_export($flags, true));
        return $this->childConnection->connect($dsninfo, $flags);
      }	
      
      /**
       * @see Connection::getDatabaseInfo()
       */
      public function getDatabaseInfo()
      {
        return $this->childConnection->getDatabaseInfo();
      }	
      
      /**
       * @see Connection::getIdGenerator()
       */
      public function getIdGenerator()	
      {
        return $this->childConnection->getIdGenerator();