<?php

/**
 * $Id$
 */
    
include_once('action/controller/Route.php');
include_once('action/controller/Request.php');
include_once('action/controller/Response.php');
include_once('action/controller/Base.php');
// include_once('action/controller/RequestProcessor.php');

class Dispatcher {

    /** logger instance */
    private static $logger;

    /** our entry point */
    public static function dispatch() {
        $logger = Logger::getInstance();
        // self::prepare();
        $logger->debug('Dispatcher is HIT!!!');
        $request  = new HTTPRequest();
        $response = new HTTPResponse();
        // $rp       = new RequestProcessor();
        try {
            $ac = ActionControllerRoute::createController($request);
            $ac->process($request, $response);
        } catch (Exception $e) {
            $logger->warn($e->getMessage());
        }
        // self::close();
    }

    // XXX.
    private static function prepare() {}
    // XXX.
    private static function close() {}

}
