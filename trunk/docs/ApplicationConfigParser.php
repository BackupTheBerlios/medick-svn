<?php
// {{{ file header
/**
 * It reads XML config file
 *
 * @author Oancea Aurelian <aurelian@locknet.ro>
 * @copyright Oancea Aurelian 2005 http://locknet.ro
 * @package ro.locknet.eLfrw.core
 * @version 0.1.0
 * 09.apr.2005 13:47:19
 * $Id: ApplicationConfigParser.php 6 2005-05-02 16:54:05Z aurelian $
 */
// }}}

class ApplicationConfigParser {

    // {{{ atributes    
    /**
     * Is the SimpleXml Object
     * @var SimpleXml Object
     * @access protected
     * @since 0.0.1
     */
    protected $sxe;

    /**
     * Is the config file
     * @var string
     * @access private
     * since 0.1.0
     */
    private $configFile;
    
    /**
     * Is the instance
     * @var LocknetConfig
     * @access protected
     * @since 0.0.4
     */
    static private $lConfigInstance=null;

    // }}}
    // {{{ public functions
    
    /**
     * Constructor.
     * Loads the xml file
     * @TODO: custom Exception!!!
     * @param String config file
     * @throw Exception when file is not found, readble or is not valid
     * @access public
     * @return void
     * @since 0.0.1
     */
    private function __construct($configFile){
        if( (!is_file($configFile)) OR (!is_readable($configFile)) ){
            throw new Exception("xml File is not readable or not found!");
        }
        $this->sxe = @simplexml_load_file($configFile);
        if($this->sxe===false){
            throw new Exception("xml File, bad format!<br />" . $php_errormsg);
        }
        $this->configFile = $configFile;
    }


    /**
     * A singleton.
     *
     * @param String Config File
     * @access public
     * @return $this instance
     * @since 0.0.4
     */
    public static function getInstance($configFile='conf/application.xml')
    {
        $hash = md5($configFile);
        if(!isset(self::$lConfigInstance[$hash])) {
            self::$lConfigInstance[$hash] = new ApplicationConfigParser($configFile);
        } 
        return self::$lConfigInstance[$hash];
    }
    
    // ---------------------------------------------> Application general configuration:

    /**
     * Application Name
     *
     * @access public
     * @return String Application Name
     * @since 0.0.1
     */
    public function getApplicationName() {
        return (string)trim($this->sxe['name']);
    }

    /**
     * Application State
     *
     * @access public
     * @return String Application State
     * @since 0.0.1
     */
    public function getApplicationState() {
        return (string)trim($this->sxe['state']);
    }
    
    /**
     * Application Render
     *
     * @access public
     * @return String Application Render
     * @since 0.0.1
     */
    public function getApplicationRender() {
        return (string)trim($this->sxe['render']);
    }
    
    // -----------------------------------------------------------> property parser
    
    /**
     * Propery parser
     *
     * @param String the property name
     * @access public
     * @return String, the property value
     * @throws Exception if the property is not found
     * @since 0.0.4
     */
    public function getProperty($name)
    {
        foreach(  $this->sxe->property as  $properties )
        {
            if($properties['name']==$name){
                return (string)trim($properties['value']);
            }
        }
        throw new Exception("Property " . $name . " not found!",101);
    }
    
    /**
     * Internal replacement for logger, render, session system property 
     * @param string system, a section in the config file
     * @param string property, a propriety in the system section
     * @throw ConfigException if the system is not defined well
     * @access private
     * @return bool: false if the value in the config file is empty, off or false, true if the value is true or on
     *         string: the string value 
     */
    private function getSubsystemProperty($system,$property) {
        if(!$this->sxe->$system){
            throw new Exception("Cannot find " . $system . " section in your Configuration File: " . $this->configFile . "!",100);
        }
        $_sys   = $this->sxe->$system->$property;
        $_query = (string)trim($_sys['value']);
        if( ($_query=='') OR ($_query=='false') OR ($_query=='off') ){
            return false;
        } elseif( ($_query=='true') OR ($_query=='on') ) {
            return true;
        } else {
            return (string)$_query;
        }

    }
    
    // -----------------------------------------------------------> render options

    public function getRenderProperty($property) {
        return $this->getSubsystemProperty('render',$property);
    }
    
    // -----------------------------------------------------------> logger options

    public function getLoggerProperty($property) {
        return $this->getSubsystemProperty('logger',$property);
    }
    
    // -----------------------------------------------------------> session options
    
    public function getSessionProperty($property) {
        return $this->getSubsystemProperty('session',$property);
    }
    
    // -----------------------------------------------------------> sonar settings
    
    public function getSonartProperty($property) {
        return $this->getSubsystemProperty('sonart',$property);
    }

    
    // -----------------------------------------------------------> database settings
    
    public function getDatabaseProperty($property) {
        return $this->getSubsystemProperty('database',$property);
    }
    
    // }}}
}
