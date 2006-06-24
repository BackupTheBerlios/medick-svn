<?php

// $Id$

include_once('medick/Registry.php');
include_once('logger/Logger.php');

include_once('mock/MockConfigurator.php');
include_once('mock/MockRequest.php');

include_once('action/controller/Route.php');
include_once('action/controller/Map.php');
// include_once('action/controller/Routing.php');
// include_once('action/controller/Base.php');

class RoutingTest extends UnitTestCase {

    // private $map;

    // set-up the registry
    public function setUp() {
        Registry::put($configurator= new MockConfigurator(), '__configurator');
        Registry::put(new Logger($configurator), '__logger');
        // $this->map= Map::getInstance();
    }
    
    // clean-up
    public function tearDown() {
        Registry::close();
    }
    
    // tests default route:
    // :controller/:action/:id
    public function testDefaultRoute() {
        // internal setup
        $r= $this->getDefaultRoute();
        $request= new MockRequest();
        $request->setRequestUri('/project/delete/3.html');
        
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('controller'), 'project');
        $this->assertEqual($request->getParameter('action'), 'delete');
        $this->assertEqual($request->getParameter('id'), 3);
        
        // basically, after a new URI try, the old request parameters should be discarded
        $request->setRequestUri('/task/list.html');
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('controller'), 'task');
        $this->assertEqual($request->getParameter('action'),'list');
        $this->assertFalse($request->hasParameter('id'));

        $request->setRequestUri('/mdk');
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('controller'), 'mdk');
        $this->assertEqual($request->getParameter('action'), 'index');

        $request->setRequestUri('news.html');
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('controller'), 'news');

        $request->setRequestUri('/news/details/5.html/foo');
        $this->assertFalse($r->match($request));

    }

    public function testSimpleStaticComponents() {
        $r= new Route('/project/all');
        
        $r->setDefaults(
            array(
                'controller'=>'project',
                'action'=>'all'
            )
        );
        $request= new MockRequest();
        
        $request->setRequestUri('/project/all.html');
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('controller'),'project');
        $this->assertEqual($request->getParameter('action'),'all');

        $request->setRequestUri('/project/index.html');
        $this->assertFalse($r->match($request));
        $request->setRequestUri('/project/delete/3.html');
        $this->assertFalse($r->match($request));
    }
    
    public function testSimpleStaticAndDynamic() {
        $routes= array();
        $routes[]= new Route('/project/all');
        $routes[]= new Route(':controller/:action/:id');
        $request= new MockRequest();
        
        $routes[0]->setDefaults(array('controller'=>'project','action'=>'all'));
        
        $request->setRequestUri('/news/details/1.html');
        $this->assertFalse($routes[0]->match($request));
        $this->assertTrue($routes[1]->match($request));

        $request->setRequestUri('/project/overview/1.html');
        $this->assertFalse($routes[0]->match($request));
        $this->assertTrue($routes[1]->match($request));
        $this->assertEqual($request->getParameter('controller'), 'project');
        $this->assertEqual($request->getParameter('action'), 'overview');

        $request->setRequestUri('/project/index.html');
        $this->assertFalse($routes[0]->match($request));
        $this->assertTrue($routes[1]->match($request));

        $request->setRequestUri('/project/all.html');
        foreach ($routes as $route) {
            if ($route->match($request)) break;
        }
        $this->assertEqual('/project/all', $route->getRouteList());
    }
    
    public function testDynamicAndStaticWithDefaults() {
        $r= new Route('/project/overview/:id');
        $r->setDefault('controller', 'project');
        $r->setDefault('action', 'overview');
        
        $request= new MockRequest();
        $request->setRequestUri('/project/overview/5.html');
        
        $this->assertTrue($r->match($request));
        $this->assertEqual($request->getParameter('id'), 5);

        $request->setRequestUri('/project/overview.html');
        $this->assertTrue($r->match($request));
        $this->assertFalse($request->hasParameter('id'));
        
        // $r= new Route('/blog/archive/:year/:month/:day');
        
    }
    
    public function testSimpleNamedRoutes() {
        $map= Map::getInstance();
        $map->add(new Route('/project/all', Route::WELCOME, array('controller'=>'project', 'action'=>'all')));
        $map->add($this->getDefaultRoute());
        $request= new MockRequest();
        
        $request->setRequestUri('/project/all.html');
        $this->assertTrue($map->getRouteByName(Route::WELCOME));
        $this->assertTrue($route= $map->match($request));
        $this->assertEqual(Route::WELCOME, $route->getName());
        /*
        foreach ($routes as $route) {
            if ($route->match($request)) break;
        }
        */
    }
    
    private function getDefaultRoute() {
        return new Route(':controller/:action/:id');
    }
    
}

