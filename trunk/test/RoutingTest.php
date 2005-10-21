<?php

// $Id$

include_once('medick/Registry.php');
include_once('logger/Logger.php');

include_once('mock/MockConfigurator.php');
include_once('mock/MockRequest.php');

include_once('action/controller/Route.php');
include_once('action/controller/Map.php');
include_once('action/controller/Routing.php');
include_once('action/controller/Base.php');

class RoutingTest extends UnitTestCase {

    private $map;

    public function setUp() {
        Registry::put(new MockConfigurator(), '__configurator');
        Registry::put(new Logger(), '__logger');
        $this->map= Registry::put(new Map(), '__map');
    }
    
    public function tearDown() {
        Registry::close();
    }
    
    public function testRec() {
        $route= new Route('foo', 'bar');
        $request= new MockRequest();
        $request->setParam('controller', 'foo');
        $request->setParam('action', 'bar');
        $this->map->add($route);
        $this->assertIsA(ActionControllerRouting::recognize($request), 'ActionControllerBase');
    }
}
