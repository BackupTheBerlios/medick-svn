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

    /** @var array list with allowed levels */
    private $levels = array('debug','info','warn','error');

    /** @var int default priority level */
    private $level = 0;

    /** @var Formatter */
    private $formatter;

    /** @var array list of IOutputters[] */
    private $outputters = array();

    /** @var LoggingEvent the event to log */
    private $event = NULL;

    /** @var int message level */
    private $messageLevel;

    /**
     * Constructor.
     * It reads the config file and setup the logging system
     */
    public function __construct() {
        $configurator = Registry::get('__configurator');
        $outputters   = $configurator->getLoggerOutputters();
        if (sizeof($outputters) != 0) {
            $this->load($outputters);
        }

        $this->setLevel(Logger::DEBUG); // TODO: check this line again please.

        if ($_klazz = $configurator->getLoggerFormatter()) {
            include_once('logger' . DIRECTORY_SEPARATOR . 'formatter' . DIRECTORY_SEPARATOR . $_klazz . '.php');
            $this->formatter= new $_klazz;
        }

        $this->debug('Logger ready');
    }

    /** __magic __overloading__ */
    public function __call($method, $message=FALSE) {
        if (!$message) return;
        if (sizeof($this->outputters) == 0) return;
        if (!in_array($method, $this->levels)) {
            trigger_error(
              sprintf('Call to undefined function: %s::%s(%s).', $this->getClassName(), $method, $message), E_USER_ERROR
            );
        }
        foreach ($this->levels as $_level=>$_name) {
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
        foreach($this->outputters as $outputter) {
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
     * @TODO: we have a base Object now, can we remove the call to getId?
     * @param IObserver $observer a observer
     * @return bool
     */
    private function contains(IOutputter $outputter) {
        foreach ($this->outputters as $out) {
            if ($out->getClassName() == $outputter->getClassName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Loads the Outputters.
     * @param array outputters
     */
    public function load(/*Array*/ $outputters) {
        foreach ($outputters as $outputter) {
            $class_name= ucfirst($outputter['name']) . 'Outputter';
            $class_file= 'logger' . DIRECTORY_SEPARATOR . 'outputter' . DIRECTORY_SEPARATOR . $class_name . '.php';
            @include_once($class_file);
            try {
                $class= new ReflectionClass($class_name);
                $instance= $class->newInstance($outputter['level']);
                if (isset($outputter['properties'])) {
                    $instance->setProperties($outputter['properties']);
                }
                $instance->initialize();
                $this->attach($instance);
            } catch (ReflectionException $rEx) {
                $this->warn($rEx->getMessage());
            }
        }
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
}
