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

/**
 * xml file-based Configurator.
 * @package locknet7.config
 */
class XMLConfigurator extends Configurator {

    /** SimpleXML Object */
    protected $sxe;

    /**
     * Constructor.
     * @param string, xml, configuration file/string
     */
    public function __construct($xml) {
        if (is_file($xml)) $this->sxe = simplexml_load_file($xml, 'SimpleXMLIterator');
        else $this->sxe = simplexml_load_string($xml, 'SimpleXMLIterator');
        if ($this->sxe===false) throw new ConfiguratorException('Cannot read ' . $xml . '\n<br /> Bad Format!');
    }

    /** @see Configurator::getSectionProperty() */
    public function getSectionProperty($section, $property) {
        if(!$this->sxe->$section) throw new ConfiguratorException('Cannot find ' . $section . ' section in your Configuration!');
        $_sys   = $this->sxe->$section->$property;
        $_query = (string)trim($_sys['value']);
        if( ($_query=='') OR ($_query=='false') OR ($_query=='off') OR ($_query == 0) ){
            return false;
        } elseif( ($_query=='true') OR ($_query=='on') OR ($_query == 1) ) {
            return true;
        } else {
            return (string)$_query;
        }
    }
    
    /** 
     * Configuration Example:
     * <code>
     *      <database default="foo">
     *          <dsn id="one"
     *               phptype  = "mysql"
     *               hostspec = "localhost"
     *               database = "todo"
     *               username = "root"
     *               password = "zzz" />
     *          <dsn id = "foo"
     *               phptype  = "pgsql"
     *               hostspec = "192.18.1.1"
     *               database ="test"
     *               username ="antonescu"
     *               password ="x-creeme" />
     *      </database>
     * </code>
     * @see Configurator::getDatabaseDsn() 
     */
    public function getDatabaseDsn($id = FALSE) {
        if (!$id) $id = $this->sxe->database['default'];
        foreach( $this->sxe->database->dsn as  $dsn ) {
            if($dsn['id']==$id){
                return array (
                    'phptype'  => (string)trim($dsn['phptype']),
                    'hostspec' => (string)trim($dsn['hostspec']),
                    'username' => (string)trim($dsn['username']),
                    'password' => (string)trim($dsn['password']),
                    'database' => (string)trim($dsn['database'])
                );
            }
        }
        throw new ConfiguratorException('Id ' . $id . 'not found!');
    }
    
    /**
     * Configuration example:
     * <code>
     *      <logger>
     *          <outputters>
     *              <outputter name="file"    level="0" value="/wwwroot/htdocs/locknet7/log/locknet7.log" />
     *              <outputter name="mail"    level="2" value="xxxx@xxxx.xxxx" />
     *              <outputter name="stdout"  level="0" />
     *          </outputters>
     *      </logger>
     * </code>
     * @see Configurator::getLoggerOutputters
     * @return SimpleXMLIterator
     */
    public function getLoggerOutputters() {
        return $this->sxe->logger->outputters;
    }
    
    /** @see Configurator::getLoggerFormatter */
    public function getLoggerFormatter() {
        return ucfirst((string)trim($this->sxe->logger->formatter) . 'Formatter');
    }
    
    /** @see Configurator::getProperty */
    public function getProperty($name) {
        foreach($this->sxe->property as  $properties ) {
            if($properties['name']==$name) {
                return (string)trim($properties['value']);
            }
        }
        throw new ConfiguratorException('Property ' . $name . ' not found!');
    }    
    
    /** 
     * Configuration Example:
     * <code>
     *      <route name="default">
     *          <controller>foo</controller>
     *          <action>someaction</action>
     *      </route>
     * </code>
     * or, to use the default medick action (index):
     * <code>
     *      <route><controller>foo</controller></route>
     * </code>
     * @see Configurator::getDefaultRoute() */
    public function getDefaultRoute() {
        return $this->sxe->route[0];
    }
}
