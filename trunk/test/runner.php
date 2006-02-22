<?php

// $Id$

// Script that runs all the *Test.php* files from the test folder.
// Read README.tests for details on running tests.

$time_start = microtime(true);

// {{{ settings
define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('TMP', 'tmp' . DIRECTORY_SEPARATOR);

set_include_path( TOP_LOCATION . 'libs' . DIRECTORY_SEPARATOR . PATH_SEPARATOR .
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . dirname(__FILE__)
                );

error_reporting(E_ALL);
set_time_limit(0);
ini_set('display_errors', 1);
// }}}

// {{{ paths
include_once('medick/Object.php');
include_once('medick/Exception.php');
include_once('medick/Registry.php');
include_once('medick/util.php');
include_once('medick/io/Folder.php');
include_once('medick/ConsoleOptions.php');
// include_once('simpletest/web_tester.php');
include_once('simpletest/unit_tester.php');
include_once('simpletest/reporter.php');
include_once('configurator/LoggerConfigurator.php');
include_once('logger/Logger.php');
// }}}

// {{{ configure console
$options= new ConsoleOptions();
$options->setNoValueFor('debug', '-d', '--debug');
$options->load(isset($argv)?$argv:$_SERVER['argv']);
$options->alias('debug', '-d, --debug');
// }}}

// {{{ logger.
$logger= new Logger(new LoggerConfigurator());
// }}}

$test= new GroupTest("=== Medick Framework Unit Tests ===");
$test_files = Folder::recursiveFindRelative('.', 'test', 'Test.php');
foreach($test_files as $file) {
    if ($options->has('debug')) {
        $logger->debug('Adding test file: ' . $file);
    }
    $test->addTestFile($file);
}

$test->run(new TextReporter());

if ($options->has('debug')) {
    $time_end = microtime(true);
    $logger->debug('Done in ' . round($time_end - $time_start,4) . ' seconds');
}

// {{{ clean-up
@unlink(TMP . 'test.db');
// }}}
