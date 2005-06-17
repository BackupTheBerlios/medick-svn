<?php
/**
 * Index file, the only one.
 * @package locknet7.start
 * @version $Revision$
 * @author
 * @copyright
 */

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config/bootstrap.php');
Dispatcher::dispatch();
