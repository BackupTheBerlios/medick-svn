<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice, 
//   this list of conditions and the following disclaimer. 
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation 
//   and/or other materials provided with the distribution. 
//   * Neither the name of locknet.ro nor the names of its contributors may 
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

include_once('action/controller/route/IRoute.php');

/**
 * @package locknet7.action.controller.route
 */
class MedickRoute implements IRoute {

    /** 
     * We recieve from request the controller in this form <tt>news</tt>
     * the internal object name for this controller will be <tt>NewsController</tt>
     */
    private $ctrl_name;
    
    /** 
     * By default, all the controllers resids in <tt>TOP_LOCATION/app/controllers/</tt>
     */
    private $ctrl_path;
    
    /**
     * By default, the controller file will be located 
     * on <tt>$controller_path/$request->getParam('controller')_controller.php</tt>
     */ 
    private $ctrl_file;
    
    /**
     * Constructor...
     * It builds this ROUTE
     * @param string, controller_name, controller name
     */
    public function __construct($controller_name) {
        $this->ctrl_name = is_null($controller_name) ? NULL : ucfirst($controller_name) . 'Controller';
        $this->ctrl_path = 
            Configurator::getInstance()->getProperty('application_path') . 
            DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $this->ctrl_file = strtolower($controller_name) . '_controller.php';
    }

    /** @see: IRoute::getControllerName */
    public function getControllerName() {
        return $this->ctrl_name;
    }

    /** @see: IRoute::getControllerPath */
    public function getControllerPath() {
        return $this->ctrl_path;
    }

    /** @see: IRoute::getControllerFile */
    public function getControllerFile() {
        return $this->ctrl_file;
    }
    
    /** a string representation of this object*/
    public function toString() {
        return $this->ctrl_name;
    }
}
 