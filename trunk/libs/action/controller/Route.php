<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Oancea Aurelian < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Oancea Aurelian nor the names of his contributors may
//   be used to endorse or promote products derived from this software without
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// $Id$
//
// ///////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * A Route Component
 *
 * @see Route
 * @package medick.action.controller
 * @subpackage routing
 * @author Oancea Aurelian
 */
class Component extends Object {

    /** @var string
        the component name */
    private $name;

    /** @var boolean
        true if this is a dynamic component */
    private $dynamic;

    /**
     * Creates a new Route Component with the given name
     *
     * @param string name the component name
     */ 
    public function Component($name) {
        $this->name= $name;
    }

    /**
     * It gets this component name
     *
     * @return string
     */ 
    public function getName() {
        return $this->name;
    }

    /**
     * Mark this component as dynamic
     *
     * @param boolean dynamic
     */ 
    public function setDynamic($dynamic) {
        $this->dynamic= (bool)$dynamic;
    }

    public function isDynamic() {
        return $this->dynamic;
    }
    
    public function toString() {
        return sprintf('{%s}-->name=%s[dynamic=%s]', $this->getClassName(), $this->name, $this->dynamic ? 'TRUE':'FALSE');
    }

}

/**
 * Route class.
 * 
 * A Route resolves the incoming Request URI to a known Controller/Action,
 * also, it will merge Route Components Names to Request parameters.
 * 
 * Usually, a Map holds the Route Definitions for an Web Application
 * and the Recogition is performed inside the ActionControllerRouting::recognize(Request $request) method.
 * 
 * Routes are defined inside __APPLICATION__PATH/conf/__APPLICATION__NAME__.routes.php file,
 * a plain php code file.
 * 
 * When we find the first Route to match the incoming URL, we try to create the controller class instance 
 * and the other routes in the map are discarded.
 *
 * Default Route:
 * <code>
 *   $route= new Route(':controller/:action/:id');
 * </code>
 * In this case, <i>:controller</i>, <i>:action</i> and <i>id</i> are dynamic Components
 * and incoming URL parameters will be coverted to Request parameters:
 * <code>
 *  // incoming URI:
 *  // /project/view/12.html
 *  $request->getParameter('controller'); // => project
 *  $request->getParameter('action'); // => view
 *  $request->getParameter('id'); // => 12
 * </code>
 * @see Map, ActionControllerRouting, Component
 * @todo more docs
 * @package medick.action.controller
 * @subpackage routing
 * @author Oancea Aurelian
 */
class Route extends Object {

    // {{{ predefined Route Names
    // default route name
    const AUTO     = 0x000;
    // welcome route, it`s used as an entry point of the application
    const WELCOME  = 0x200;
    // used when you want to write your custom error route
    const ERROR    = 0x500;
    // used when the page is not found
    const NOTFOUND = 0x400;
    // }}}
    
    /** @var array
        this route parameters witch will be merged to request parameters */ 
    private $merges=array();
    
    /** @var array
        cheap cache, this way we can remove request parameters 
        between 2 route recogitions */
    static private $old_merges= array();
    
    /** @var array
        cheap cache for old defaults */
    static private $old_defaults= array();
    
    /** @var string
        incoming Route Definition list. */
    private $route_list;

    /** @var array
        a list with default values */
    private $defaults;

    /** @var array
        a list with this route requirements */
    private $requirements;
    
    /** @var bool
        flag to indicate that this route is loaded.
        on initial phase, we will use this flag for knowing 
        if we already loaded the defaults values 
        Later on this will be also used for validating Route Requirements. */
    private $isLoaded;
    
    /** @var string
        the route name */
    private $name;

    /** @var Collection
        route components */
    private $components;

    /** @var string
        Route Controller */
    private $controller;

    /**
     * Creates a new Route
     *
     * @param string route_list the route list
     * @param string name route name
     * @param array defaults a list of defaults values
     * @param array requirements the route requirements
     */
    public function Route($route_list, $name = '', /*Array*/ $defaults = array(), /*Array*/ $requirements = array()) {
        $this->components   = new Collection();
        $this->route_list   = $route_list;
        $this->defaults     = $defaults;
        $this->requirements = $requirements;
        $this->name         = $name=='' ? Route::AUTO : $name;
        $this->isLoaded     = false;
        $this->loadComponents();
    }
    
    /**
     * It gets the name of this Route.
     * 
     * @see Route::getNameToHuman
     * @return string name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * It sets the name of this Route.
     *
     * @param string name, the name of this Route.
     */
    public function setName($name) {
        $this->name= (string)$name;
    }

    /**
     * Sets defaults for this Route.
     *
     * @param array an array witch holds defaults values for this Route.
     */
    public function setDefaults(/*Array*/ $defaults=array()) {
        $this->defaults= $defaults;
    }
    
    /**
     * Adds only a default pair name/value on to the defaults
     *
     * @param string name
     * @param string value
     */ 
    public function setDefault($name, $value) {
        $this->defaults[$name]= $value;
    }
    
    /**
     * Sets an array of route requirements
     * 
     * @param array the list of requirements
     */
    public function setRequirements(/*Array*/ $requirements= array()) {
        foreach ($requirements as $name=>$value) $this->setRequirement($name, $value);
    }

    /**
     * Sets a route requirement
     * 
     * @param string
     * @param string
     */
    public function setRequirement($name, $value) {
        $this->requirements[$name]=$value;
    }
    
    /**
     * Gets this Route List Definition
     *
     * @return string the route list definition
     */ 
    public function getRouteList() {
        return $this->route_list;
    }
    
    /**
     * It gets a human readable route name for predefined Route Names
     *
     * @return string the route name
     */ 
    public function getNameToHuman() {
        switch ($this->name) {
            case Route::NOTFOUND:
                return 'NOTFOUND';
            case Route::ERROR:
                return 'ERROR';
            case Route::WELCOME:
                return 'WELCOME';
            case Route::AUTO:
                return 'AUTO';
            default:
                return $this->name;
        }
    }
    
    /** 
     * Match the current Route against incoming URI.
     *
     * @param Request request incoming request
     * @todo refactor.
     * @return bool
     */ 
    public function match(Request $request) {
        $parts= $request->getUriParts();
        
        // if we have more parameters passed, as expected.
        if (count($parts) > $this->components->size()) {
            return false;
        }
        // if / was requested, just skip this part.
        if ( count($parts) != 0 ) {
            $it= $this->components->iterator();
            $this->merges= array();
            while($it->hasNext()) {
                $component = $it->next();
                if (isset($parts[$it->key()])) {
                    if (!$component->isDynamic() && $component->getName() != $this->ignoreExtension($parts[$it->key()]) ) {
                        return false;
                    } elseif (
                        isset($this->requirements[$component->getName()]) 
                            && 
                        !preg_match($this->requirements[$component->getName()], $parts[$it->key()]) )
                    {
                        return false;
                    } else {
                        $this->merges[$component->getName()] = $this->ignoreExtension($parts[$it->key()]);
                    }
                }
            }
        }
    
        // preparing to return true.
        $this->doMerge($request);
        $this->load($request);
        return true;
    }
    
    /**
     * Creates a Controller Instance
     * 
     * @throws RoutingException
     * @return ActionControllerBase
     */ 
    public function createControllerInstance(Request $request) {
        if (!$this->isLoaded) $this->load($request);
        try {
            // Registry::get('__logger')->debug($this->toString());
            return Registry::put(new Injector(), '__injector')->inject('controller', $request->getParameter('controller'));
        } catch (FileNotFoundException $fnfEx) {
            throw new RoutingException('Cannot create a controller instance, ' . $fnfEx->getMessage());
        }
    }
    
    /**
     * A String representation of this Route
     *
     * @return string
     */ 
    public function toString() {
        return sprintf('{%s}-->Name: %s; List: %s;', 
                        $this->getClassName(), 
                        $this->getNameToHuman(), 
                        $this->route_list);
    }
    
    /**
     * Helper method, will remove everithing after . in parts
     * 
     * @param string
     * @return string
     */ 
    private function ignoreExtension($on) {
        if (false === strpos($on, '.html')) {
            $part = $on;
        } else {
            list($part)= explode('.', $on);
        }
        return $part;
    }
    
    /**
     * Merges this Route Parameters into Request Parameters
     *
     * @param Request request, the request on witch we want to merge
     */ 
    private function doMerge(Request $request) {
        foreach ($this->merges as $name=>$value) {
            if (isset(Route::$old_merges[$name])) unset(Route::$old_merges[$name]);
            $request->setParameter($name, $value);
        }
        // discard previously route parameters.
        foreach (Route::$old_merges as $name=>$value) {
            $request->setParameter($name, NULL);
        }
        // cache merged parameters  
        Route::$old_merges= $this->merges;
    }

    /**
     * Trigger method to load defaults values and for setting a propper action and controller
     */ 
    private function load(Request $request) {
        $this->loadDefaults($request);
        $this->loadActionAndController($request);
        $this->isLoaded= true;
    }
    
    private function loadDefaults(Request $request) {
        foreach ($this->defaults as $name=>$value) {
            if (isset(Route::$old_defaults[$name])) unset(Route::$old_defaults[$name]);
            $request->setParameter($name, $value);
        }
        foreach (Route::$old_defaults as $name=>$value) {
            $request->setParameter($name, NULL);
        }
        Route::$old_defaults= $this->defaults;
    }
    
    /**
     * Loads Special Parameters: Controller and Action
     *
     * @throws RoutingException if a controller cannot be resolved for this route
     */ 
    private function loadActionAndController(Request $request) {
        // check if we have a controller.
        if (!$request->hasParameter('controller') || $request->getParameter('controller') == '') {
            throw new RoutingException('Cannot Resolve A Controller for this Route!');
        }
        // check for an action
        if (!$request->hasParameter('action') || $request->getParameter('action') == '') {
            $request->setParameter('action','index');
        }
    }
    
    /**
     * Helper Method for loading the Route Components
     * 
     * @return void
     */ 
    private function loadComponents() {
        $parts= explode('/', trim($this->route_list, '/'));
        foreach ($parts as $key=>$element) {
            if (preg_match('/:[a-z0-9_\-]+/',$element, $match)) {
                $c= new Component(substr(trim($match[0]), 1));
                $c->setDynamic(true);
            } else {
                $c= new Component($element);
                $c->setDynamic(false);
            }
            $this->components->add($c);
        }
    }

}
