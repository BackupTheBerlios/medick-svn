<?php

/**
 * Will bootstrap the application by setting it`s propreties.
 * Required files are included here, the php include_path will point to our libs folder.
 * Here is the place where we start the first logging instance.
 * 
 * @package locknet7.start
 * @author Oancea Aurelian 
 * $Id $
 */

error_reporting(E_ALL);

// main TOP_LOCATION.
define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

// include_path, rewrite the existing one 
set_include_path( TOP_LOCATION . 'libs' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'app'  . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .  
                  TOP_LOCATION . 'app'  . DIRECTORY_SEPARATOR . 'models'      . DIRECTORY_SEPARATOR 
                );

// {{{ Logger Setup
include_once('logger/Logger.php');
$logger = Logger::getInstance();
$logger->attach(new StdoutOutputter());
// $logger->attach(new JavaScriptOutputter());
$logger->attach(new FileOutputter(TOP_LOCATION . 'log' . DIRECTORY_SEPARATOR . 'locknet7.log'));
$logger->attach(new MailOutputter('XXXXX@XXXXX.XXXXX', 'Fatality...'));
$logger->setLevel(Logger::DEBUG);
$logger->setFormatter(new SimpleFormatter());
// $logger->setFormatter(new DefaultFormatter());
$logger->debug('Logger ready');
// }}}

include_once('Dispatcher.php');
