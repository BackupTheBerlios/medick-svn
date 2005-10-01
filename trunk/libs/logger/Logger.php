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

include_once('logger/ILogger.php');
include_once('logger/LoggingException.php');
include_once('logger/LoggingEvent.php');
include_once('logger/outputter/IOutputter.php');
include_once('logger/outputter/Outputter.php');
include_once('logger/formatter/Formatter.php');

/**
 * @package locknet7.logger
 */
 
class Logger extends Object implements ILogger {

    /** a fancy way of telling the level */
    const DEBUG = 0;
    const INFO  = 1; 
    const WARN  = 2;
    const ERROR = 3;
    
    /** list with allowed levels */
    private $levels = array('debug','info','warn','error');

    /** default priority level */
    private $level = 0;
    
    /** formatter */
    private $formatter;

    /** this logger instance */
    private static $instance = NULL;

    /** list of appenders */
    private $outputters = array();

    /** the event to log */
    private $event = NULL;
    
    /** message level */
    private $messageLevel;
    
    /**
     * Constructor.
     * It reads the config file and setup the logging system 
     */
    private final function __construct() {
    
        $configurator = MedickRegistry::get('__configurator');
        $outputters   = $configurator->getLoggerOutputters();

        for ($outputters->rewind(); $outputters->valid(); $outputters->next()) {   
            foreach($outputters->getChildren() as $outputter) {
                try {
                    $class_name= ucfirst((string)trim($outputter['name'])) . 'Outputter';
                    $class_file= 'logger' . DIRECTORY_SEPARATOR . 'outputter' . DIRECTORY_SEPARATOR . $class_name . '.php';
                    include_once($class_file);
                    $class= new ReflectionClass($class_name);
                    $this->attach( 
                        $class->newInstance( (string)trim($outputter['level']), (string)trim($outputter['value']) ));
                } catch (ReflectionException $rEx) {
                    $this->warn($rEx->getMessage());
                }
            }
        }
        $this->setLevel(Logger::DEBUG);
        $_klazz = $configurator->getLoggerFormatter();
        include_once('logger' . DIRECTORY_SEPARATOR . 'formatter' . DIRECTORY_SEPARATOR . $_klazz . '.php');
        $this->formatter= new $_klazz;
        $this->debug('Logger ready');
    }

    /** the pefect singleton :) */
    private final function __clone() {      }
    
    /** __magic __overloading__ */
    public function __call($method, $message) {
        if (!$message) return;
        if (!in_array($method, $this->levels)) {
            trigger_error(
              sprintf('Call to undefined function: %s::%s(%s).', get_class($this), $method, $message), E_USER_ERROR
            );
        }
        foreach ($this->levels AS $_level=>$_name) {
            if($_name == $method) break;
        }
        if ($_level < $this->level) return;
        $this->messageLevel = $_level;
        $this->event = new LoggingEvent($message[0], $method);
        $this->notify();
    }
    
    /**
     * It logs a message.
     * @param mixed, $message, the message to log.
     * @param Logger, $level, the level, it can be Logger::DEBUG (0), Logger::INFO (1)...
     * @return void
     */ 
    public function log($message, $level) {
        return $this->__call($this->levels[$level], $message);
    }
    
    /**
     * Notify the outputters
     * @return void
     */
    public function notify() {
        foreach($this->outputters AS $outputter) {
            $outputter->update($this);
        }
    }
    
    /**
     * Attach an outputter
     * @param IOutputter, outputter, the outputter
     * @return void
     */ 
    public function attach(IOutputter $outputter) {
        if (!$this->contains($outputter)) {
            $this->outputters[] = $outputter;
        }
    }
    
    /**
     * check to see if the list outputters contains the given outputter.
     * @param IObserver $observer a observer
     * @return bool
     */
    private function contains(IOutputter $outputter) {
        foreach ($this->outputters as $out) {
            if ($out->getId() == $outputter->getId()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * It gets the list with attached outputters
     * @return IOutputter[]
     */ 
    public function getOutputters() {
        return $this->outputters;
    }
    
    /**
     * It gets the last message level
     * @return int, the message level
     */
    public function getMessageLevel() {
        return $this->messageLevel;
    }    
    
    /**
     * It gets the event
     * @return LoggingEvent
     */
    public function getEvent() {
        return $this->event;
    }
    
    /**
     * Set`s the event formatter
     * @param Formatter formatter, the formatter
     * @return void
     */ 
    public function setFormatter(Formatter $formatter) {
        $this->formatter = $formatter;
    }
    
    /**
     * It gets the formatter
     * @return Formatter
     */ 
    public function getFormatter() {
        return $this->formatter;
    }
    
    /**
     * It sets the logging level
     * @param Logger, $level, the level, it can be Logger::DEBUG (0), Logger::INFO (1)...
     * @return void
     */ 
    public function setLevel($level) {
        $this->level = $level;
    }
    
    /**
     * It gets the level.
     * @return the logging level
     */ 
    public function getLevel() {
        return $this->level;
    }
    
    /**
     * returns this instance of logger.
     * @return Logger, a logger instance
     */  
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }
}
