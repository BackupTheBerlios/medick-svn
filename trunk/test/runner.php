#!/wwwroot/bin/php -q
<?php

// $Id$

// Script that runs all the *Test.php* files from the test folder.

class DirectoryTreeIterator extends RecursiveIteratorIterator
{
    /** Construct from a path.
     * @param $path directory to iterate
     */
    function __construct($path)
    {
        parent::__construct(
            new RecursiveCachingIterator(
                new RecursiveDirectoryIterator($path), CachingIterator::CALL_TOSTRING|CachingIterator::CATCH_GET_CHILD), 1);
    }

    /** Aggregates the inner iterator
     */
    function __call($func, $params)
    {
        return call_user_func_array(array($this->getSubIterator(), $func), $params);
    }
}

$time_start = microtime(true);

define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('TMP', 'tmp' . DIRECTORY_SEPARATOR);

set_include_path( TOP_LOCATION . 'libs' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . dirname(__FILE__)
                );

error_reporting(E_ALL);
set_time_limit(0);
ini_set('display_errors', 1);

include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/Registry.php');
include_once('medick/Collection.php');

// include_once('simpletest/web_tester.php');
include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');

$test= new GroupTest('====== Medick Framework Unit Tests =====');

foreach(new DirectoryTreeIterator(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR) as $entry)
{
    if ( (strpos($entry->getFilename(), 'Test.php')  !== FALSE) &&
         (strpos($entry->getFilename(), 'Test.php.') === FALSE) ) {
        $test->addTestFile($entry->getPathname());
    }
}

$test->run(new TextReporter());

$time_end = microtime(true);
echo "Done in " . ($time_end - $time_start) . " seconds\n";

@unlink(TMP . 'test.db');

