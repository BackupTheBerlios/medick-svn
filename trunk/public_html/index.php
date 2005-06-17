<?php
/**
 * Index file, the only one.
 * @package locknet7.start
 * @author Oancea Aurelian
 * $Id$
 */

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'bootstrap.php');
Dispatcher::dispatch();
