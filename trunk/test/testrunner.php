#!/usr/local/bin/php -q
<?php

// $Id$

// Script that runs all the *Test.php* files from the current folder.
    
$time_start = microtime(true);

define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

set_include_path( TOP_LOCATION . 'libs' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'app'  . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .
                  dirname(__FILE__) 
                );

error_reporting(E_ALL);
set_time_limit(0);

include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/Registry.php');
include_once('medick/Collection.php');

include_once('simpletest/web_tester.php');
include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');


// {{{ RecursiveTestLoader
// loads the test
// TODO: refactor.
class RecursiveTestLoader extends RecursiveIteratorIterator {

    private static $test;
	
    /** Constructor */
	function __construct() {
		parent::__construct(
		  new CachingRecursiveIterator(
		      new RecursiveDirectoryIterator(
		          dirname(__FILE__)
		      ), 
		      CIT_CALL_TOSTRING|CIT_CATCH_GET_CHILD
		  ),
		1);   
		self::$test= new GroupTest('====== Routes :: Medick Framework Unit Tests =====');
        }

	/** @return the current element valid element */
	function current() {
		$tree = '';
		for ($l=0; $l < $this->getDepth(); $l++) {
			$tree .= $this->getSubIterator($l)->hasNext() ? '| ' : '  ';
		}
        if (preg_match("/Test.php/i", $this->key()) && is_file($this->key()) && !preg_match("/.svn/i", $this->key())) {
            self::$test->addTestFile($this->key());
        }
	}

	/** Aggregates the inner iterator */
	function __call($func, $params) {
		return call_user_func_array(array($this->getSubIterator(), $func), $params);
	}
	
	public static function getTest() {
	   return self::$test;
	}
	
}

foreach(new RecursiveTestLoader() as $test) { }

RecursiveTestLoader::getTest()->run(new TextReporter());

$time_end = microtime(true);
echo "Done in " . ($time_end - $time_start) . " seconds\n";

