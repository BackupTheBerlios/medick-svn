<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
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
 * @package locknet7.action.controller.route
 */
class Component extends Object {

    private $name;

    private $dynamic;

    private $position;

    public function Component($name) {
        $this->name= $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setDynamic($dynamic) {
        $this->dynamic= (bool)$dynamic;
    }

    public function isDynamic() {
        return $this->dynamic;
    }

    public function setPosition($position) {
        $this->position= (int)$position;
    }

    public function getPosition() {
        return $this->position;
    }

    public function toString() {
        return sprintf('{%s}--->name=%s[dynamic=%s]', $this->getClassName(), $this->name, $this->dynamic ? 'TRUE':'FALSE');
    }

}

/**
 * Route class.
 *
 * @package locknet7.action.controller.route
 */
class Route extends Object {

    const WELCOME  = 0x200;

    const ERROR    = 0x500;

    const NOTFOUND = 0x400;

    /** @var string
        incoming Route Definition list. */
    private $route_list;

    /** @var array
        a list with default values */
    private $defaults;

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
     * @param array
     */
    public function Route($route_list, $name = '', /*Array*/ $defaults = array(), /*Array*/ $requirements = array()) {

        $this->components = new Collection();
        $this->route_list = $route_list;
        $this->defaults   = $defaults;
        $this->name       = $name;

        $parts= explode('/', trim($this->route_list, '/'));

        foreach ($parts as $key=>$element) {
            // Registry::get('__logger')->debug('Parts:: [' . $key . '] '. $element);
            if (preg_match('/:[a-z0-9_\-]+/',$element, $match)) {
                $c= new Component(substr(trim($match[0]), 1));
                $c->setDynamic(TRUE);
            } else {
                $c= new Component($element);
                $c->setDynamic(FALSE);
            }
            $c->setPosition($key);
            $this->components->add($c);
        }
    }

    /**
     * It gets the name of this Route.
     *
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
    public function setDefaults(/*Array*/ $defaults) {
        $this->defaults= $defaults;
    }

    // match the current Route against incoming URL.
    // @TODO: refactor.
    // @return bool
    public function match(Request $request) {
        $parts= $request->getPathInfoParts();

        // also if we have more parameters passed, as expected.
        if ( count($parts) > $this->components->size()) {
            return FALSE;
        }
        // if / was requested, just skip this part.
        if ( count($parts) != 0 ) {
            $it= $this->components->iterator();
            while($it->hasNext()) {
                $name= $it->next()->getName();

                if (isset($parts[$it->key()])) {

                    if (FALSE===strpos($parts[$it->key()], '.html')) {
                        $part= $parts[$it->key()];
                    } else {
                        list($part)= explode('.', $parts[$it->key()]);
                    }
                    $request->setParameter($name, $part);
                }

            }
        }

        // more to be done.

        // check if we have a controller.
        if (!$request->hasParameter('controller')) {
            if (array_key_exists('controller', $this->defaults)) {
                $request->setParameter('controller', $this->defaults['controller']);
            } else { // we don`t have a controller for this route (?), exit.
                return FALSE;
            }
        }

        // check for an action
        if (!$request->hasParameter('action')) {
            if (array_key_exists('action', $this->defaults)) {
                $request->setParameter('action', $this->defaults['action']);
            } else {
                $request->setParameter('action','index');
            }
        }

        $this->controller= $request->getParameter('controller');
        Registry::get('__logger')->debug($this->toString());
        return TRUE;
    }

    public function createControllerInstance() {
        if ($this->controller === NULL) {
            throw new RoutingException('Cannot resolve a controller for this Route!');
        }
        try {
            return Registry::put(new Injector(), '__injector')->inject('controller', $this->controller);
        } catch (FileNotFoundException $fnfEx) {
            throw new RoutingException('Cannot create a controller instance,' . $fnfEx->getMessage());
        }
    }
    
    /**
     * A String representation of this Route
     *
     * @return string
     */ 
    public function toString() {
        return "{".$this->getClassName()."}-->{$this->route_list}";
    }
    
}

