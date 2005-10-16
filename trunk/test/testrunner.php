#!/wwwroot/bin/php -q
<?php

// $Id$

// Script that runs all the *Test.php* files from the current folder.
    
$time_start = microtime(true);

define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

set_include_path( TOP_LOCATION . 'libs' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  dirname(__FILE__) 
                );

error_reporting(E_ALL);
set_time_limit(0);
ini_set('display_errors', 1);

include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/Registry.php');
include_once('medick/Collection.php');

include_once('simpletest/web_tester.php');
include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');

if (is_file('test.db')) unlink('test.db');

$query=<<<__
    CREATE TABLE authors (
        id INTEGER PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(150)
    );
__;
sqlite_query(sqlite_open('test.db'), $query);

include_once('mock/MockConfigurator.php');
Registry::put(new MockConfigurator(), '__configurator');
include_once('logger/Logger.php');
Registry::put(new Logger(), '__logger');

$test= new GroupTest('====== Medick Framework Unit Tests =====');

$it = new DirectoryIterator(dirname(__FILE__));
foreach($it as $file) {
    if ($file->isDir()) continue;
    if (preg_match("/Test.php/i", $file->getFileName())) $test->addTestFile($file->getFileName());
}

$test->run(new TextReporter());

$time_end = microtime(true);
echo "Done in " . ($time_end - $time_start) . " seconds\n";

unlink('test.db');
