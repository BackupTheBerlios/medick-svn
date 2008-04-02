<?php
//
// $Id: Session.php 431 2007-06-12 14:37:19Z aurelian $
//

/**
 * A wrapper around PHP session handling
 *
 * @package medick.action.controller
 * @subpackage http
 * @author Aurelian Oancea
 */
class HTTPSession extends Object {

    /** @var bool
        started flag */
    private $isStarted = FALSE;

    /** @var ISessionContainer
        the session container */
    private $container = NULL;
  
    /** @var string
        Session name */
    private $name = 'MSESSID';
    
    /**
     * Constructor, creates a new session object
     *
     * @throws IllegalStateException if the session is started
     */
    public function Session () {
        if ($this->isStarted) {
            throw new IllegalStateException('Session already Started!');
        }
    }

    /**
     * Starts a new Session
     *
     * Also, it setup our session preferences
     * @return void
     * @throws IllegalStateException if the session is already started
     */
    public function start() {
        if ($this->isStarted) {
            throw new IllegalStateException('Session already Started!');
        }
        
        // TODO: more settings
        // session_cache_limiter("nocache");
        session_name($this->name);
        if ($this->container!==NULL) {
            session_set_save_handler(
                array($this->container, 'open'),
                array($this->container, 'close'),
                array($this->container, 'read'),
                array($this->container, 'write'),
                array($this->container, 'destroy'),
                array($this->container, 'gc'));
        }
        session_start();
        //session_regenerate_id(TRUE);
        $this->isStarted= TRUE;
    }

    /**
     * It sets the Session Name.
     * 
     * Medick uses MSESSID as a session name identifier, overwritting PHPSESSID
     * @param string the session name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * It gets this Session name
     *
     * @return string the Session Name
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Sets a session variable
     *
     * @param string name, the name of the session variable
     * @param mixed value, the value of the variable to set
     * @return void
     */
    public function putValue($name, $value) {
        $this->checkState();
        $_SESSION[$name] = $value;
    }

    /**
     * Gets a session variable value
     *
     * @param string name, the name of the session variable
     * @return NULL if the variable is not set, or mixed, the variable value
     */
    public function getValue($name) {
        return $this->hasValue($name) ? $_SESSION[$name] : NULL;
    }

    /**
     * Check if this session has a variable with the given name
     *
     * @param string name, the name of the session variable
     * @return bool, TRUE if it has
     */
    public function hasValue($name) {
        $this->checkState();
        return isset($_SESSION[$name]);
    }

    /**
     * Remove the session value with the given name
     *
     * @param string name, the name of the session variable
     * @return void
     */
    public function removeValue($name) {
        $this->checkState();
        // unset($_SESSION[$name]);
        session_unregister($name);
    }

    /**
     * It gets the session id
     *
     * @return mixed, the session id
     */
    public function getId(){
        $this->checkState();
        return session_id();
    }

    /**
     * It sets the session container
     *
     * @param ISessionContainer container to set
     * @return void
     */
    public function setContainer(ISessionContainer $container) {
        $this->container= $container;
    }

    /**
     * It dumps the session
     *
     * @return array
     */
    public function dump() {
        $this->checkState();
        return $_SESSION;
    }

    /**
     * Alias for Session::dump()
     *
     * @see Session::dump
     */ 
    public function getValues() {
        return $this->dump();
    }
    
    /**
     * It checks the session state
     *
     * This method is called internally to ensure that the session is started before using it.
     * @return TRUE if the session is started
     */
    protected function checkState() {
        if (!$this->isStarted) {
            $this->start();
        }
        return TRUE;
    }
      
}
