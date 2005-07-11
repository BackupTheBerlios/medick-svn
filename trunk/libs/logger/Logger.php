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

/**
 * @package locknet7.logger
 */
 
class Logger implements ILogger {

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
    
        $configurator = Configurator::getInstance();
        $outputters   = $configurator->getLoggerOutputters();

        for ($outputters->rewind(); $outputters->valid(); $outputters->next()) {   
            foreach($outputters->getChildren() as $outputter) {
                try {
                    $class= new ReflectionClass(ucfirst((string)trim($outputter['name'])) . 'Outputter');
                    $this->attach( $class->newInstance( (string)trim($outputter['level']), (string)trim($outputter['value']) ));
                } catch (ReflectionException $rEx) {
                    $this->warn($rEx->getMessage());
                }
            }
        }
        $this->setLevel(Logger::DEBUG);
        $_klazz = $configurator->getLoggerFormatter();
        $this->setFormatter(new $_klazz);
        $this->debug('Logger ready');
    }

    /** the pefect singleton :) */
    private final function __clone() {      }
    
    /** __magic __overloading__ */
    public function __call($method, $message) {
        if (!$message) return;
        if (!in_array($method, $this->levels)) {
            trigger_error(sprintf('Call to undefined function: %s::%s(%s).', get_class($this), $method, $message), E_USER_ERROR);
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
    
    /** Q: is this usefull? */
    public function detach(IOutputter $outputter) {
        if ($this->contains(outputter)) {
            unset($this->outputters[$outputter]);
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


// {{{ Outputters

/** Abstract Outputter */
abstract class Outputter implements IOutputter {

    /** individual outputter level*/
    protected $level;
    
    /**
     * Receive the Logger update 
     * and writes the log event using to the formatter
     */
    public function update(ILogger $logger) {
        if ($this->level <= $logger->getMessageLevel()){
            $this->write($logger->getFormatter()->format($logger->getEvent()));
        }
    }
    
    // {{{ abstract methods
    /** gets the outputter Id, usually the outputter class name */
    public abstract function getId();
    /** it writes the message */
    protected abstract function write($message);
    // }}}
}

/** standard output */
class StdoutOutputter extends Outputter {
    
    /** the sapi name */
    private $isCLI = FALSE;
    /** end of line style (can be \n for cli or html)*/
    private $eol;
    /** buffer */
    private $output;
    
    /**
     * It builds a new StdOutputter
     * @param int, level, logger level.
     */
    public function __construct($level) {
        if (php_sapi_name() == 'cli') {
            $this->isCLI = TRUE;
            $this->eol = "\n";
        } else {
            $this->output .= '<table border="1" style="font-family: verdana;font-size: 0.7em;" width="100%"><tr><td>';
            $this->eol =  '</td></tr><tr><td>';
        }
        $this->level = $level;
    }
    
    /** It flushes (echoes) the output buffer on exit */
    public function __destruct() {
        if ($this->isCLI) {
            $this->output .= $this->eol;
        } else {
            $this->output .= '</td></tr></table>';
        }
        echo $this->output;
    }
    
    /** it writes the message to the buffer */
    protected function write($message) {
        $this->output .= $message . $this->eol;
    }
    
    /** It gets the output */ 
    public function getOutput() {
        return $this->output;
    }
    
    /** @return this class name as an identifier */ 
    public function getId() {
        return __CLASS__;
    }
}

/** it writes logging messages to a file */
class FileOutputter extends Outputter {
    
    /** file handler */
    private $handler;
    
    /**
     * It builds this outputter 
     * @param int, level, this outputter individual level
     * @param string the file to write on
     */
    public function __construct($level, $file) {
        $this->handler = fopen($file, 'a');
        $this->level = $level;
    }
    
    /**
     * Closes the handler on exit
     */
    public function __destruct() {
        if ($this->handler) {
            fclose($this->handler);
        }
    }
    
    /** it writes the message */
    protected function write($message) {
        if (flock($this->handler, LOCK_EX|LOCK_NB)) {
            fwrite($this->handler, $message . "\n");
        }
        return;
    }

    public function getId() {
        return __CLASS__;
    }
}

/**
 * @TODO: ignore multiple messages by using a file pid (or smthing)
 * It sends an email message with the logger event
 */
class MailOutputter extends Outputter {
    
    /** the email address */
    private $email;
    /** email subject */
    private $subject;

    public function __construct($level, $email, $subject='Fatality...') {
        $this->level   = $level;
        $this->email   = $email;
        $this->subject = $subject;
    }
    public function getId() {
        return __CLASS__;
    }
    
    protected function write($message) {
        @mail($this->email, $this->subject, $message);
    }
}
// }}}

// {{{ Formatters

/** Base abstract Event Formatter. */ 
abstract class Formatter {
    /**
     * Formats the event
     * @param LoggingEvent event, the event
     * @return string
     */ 
    abstract public function format(LoggingEvent $event);

    /**
     * Returns the string representation of the message data.
     * @param  mixed $message   The original message data.  This may be a
     *                          string or any object.
     * @return string           The string representation of the message.
     * @access protected
     */
    protected function extractMessage($message) {
        if(!is_string($message)) $message = print_r($message, TRUE);
        return $message;
    }
}

/** Default formatter. */ 
class DefaultFormatter extends Formatter {
    /** @see Formatter::formatter */
    public function format(LoggingEvent $event) {
        return $event->level . " >>> " . $event->message;
    }
}

/** Simple formatter */
class SimpleFormatter extends Formatter {
    /** @see Formatter::format */
    public function format(LoggingEvent $event) {
        $_oo = $event->class ? $event->class . '::' . $event->function : $event->function;
        return strftime("%d/%m/%Y %H:%M:%S", $event->date) . 
               ' [ ' . $event->file . ' / ' . $event->line . ' ] ' . 
               $_oo . ' '. $event->level . ' >>> ' . $this->extractMessage($event->message);
    }
}

// }}}

// {{{ Logging Event

class LoggingEvent {
    /** @var array */
    public $backtrace = array();
    /** @var mixed */
    public $message;
    /** @var int */
    public $level;
    /** @var string */
    public $file;
    /** @var int */
    public $line;
    /** @var string */
    public $function;
    /** @var string */
    public $class;
    /** @var string */
    public $date;
    
    /**
     * Constructor, set`s the properties
     * @param mixed, message, the message
     * @param string, this event level
     */ 
    public function __construct($message, $level) {
        $this->backtrace = debug_backtrace();
        $this->message   = $message;
        $this->level     = strtoupper($level);
        $this->date      = time();
        $this->file      = end(@explode(DIRECTORY_SEPARATOR,@$this->backtrace[1]['file']));
        $this->line      = @$this->backtrace[2]['line'];
        $this->class     = @$this->backtrace[3]['class'];
        $this->function  = @$this->backtrace[3]['function'];
    }
}

// }}}

// {{{ Interfaces

interface IOutputter {
    function update(ILogger $logger);
}

interface ILogger {
    function attach(IOutputter $appender);
    function detach(IOutputter $appender);
    function notify();
}
// }}}

