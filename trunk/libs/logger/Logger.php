<?php
/**
 * @package locknet7.logger
 * @author Oancea Aurelian
 * $Id$
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
    private $level =0;
    
    /** formatter */
    private $formatter;

    /** this logger instance */
    private static $instance = NULL;

    /** list of appenders */
    private $outputters = array();

    /** the event to log */
    private $event = NULL;
    
    /** constructor. not so sure about his responsabilities */
    private final function __construct() {  }

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
     * check to see if the list with observers contains the given observer.
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
    
    // xxx
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
    
    public function update(ILogger $logger) {
        $this->write($logger->getFormatter()->format($logger->getEvent())); 
    }
    
    public abstract function getId();
    protected abstract function write($message);
}

/** a java script pop-up */
class JavaScriptOutputter extends Outputter {

    private $code;
    
    public function __construct() {
        $this->code  = "<script language=\"javascript\">\n";
        $this->code .= "\ndebugWindow = window.open(\"\",\"debugWindow\",\"width=600,height=500,scrollbars=yes,resizable=yes\");\n";
        $this->code .= "</script>\n";
    }

    public function write($message) {
        $this->code .= "<script language=\"javascript\">\n" .
                       "    debugWindow.document.write(\"<span style='font-family: verdana;font-size: 0.7em;'>". $message . "</span><br />\");\n";
        $this->code .= "</script>\n";
    }

    public function getId() {
        return __CLASS__;
    }
    
    public function __destruct() {
        echo $this->code;
    }
}

/** standard output */
class StdoutOutputter extends Outputter {

    private $isCLI = FALSE;
    private $eol;
    private $output;
    
    public function __construct() {
        if (php_sapi_name() == 'cli') {
            $this->isCLI = TRUE;
            $this->eol = "\n";
        } else {
            $this->output .= '<table border="1" style="font-family: verdana;font-size: 0.7em;" width="100%"><tr><td>';
            $this->eol =  '</td></tr><tr><td>';
        }
    }

    public function __destruct() {
        if ($this->isCLI) {
            $this->output .= $this->eol;
        } else {
            $this->output .= '</td></tr></table>';
        }
        echo $this->output;
    }

    protected function write($message) {
        $this->output .= $message . $this->eol;
    }
    
    public function flush() {
        return $this->output;
    }
    
    public function getId() {
        return __CLASS__;
    }
}

/** it writes logging messages to a file */
class FileOutputter extends Outputter {

    private $handler;

    public function __construct($file) {
        $this->handler = fopen($file, 'a');
    }
    
    public function __destruct() {
        if ($this->handler) {
            fclose($this->handler);
        }
    }
    /** todo: add file locking*/
    protected function write($message) {
        fwrite($this->handler, $message . "\n");
    }

    public function getId() {
        return __CLASS__;
    }
}

class MailOutputter extends Outputter {

    private $mail;
    private $subject;

    public function __construct($mail, $subject) {
        $this->mail = $mail;
        $this->subject = $subject;
    }
    public function getId() {
        return __CLASS__;
    }
    
    protected function write($message) {
        @mail($this->mail, $this->subject, $message);
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
     * @param int, this event level
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

