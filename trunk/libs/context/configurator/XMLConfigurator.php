<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
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

include_once('context/configurator/IConfigurator.php');

/**
 * XML file-based Configurator
 * 
 * @package medick.configurator
 * @author Aurelian Oancea
 */

class XMLConfigurator extends Object implements IConfigurator {

    /** @var SimpleXML */
    protected $sxe;
    
    /** @var string
        application name */
    protected $application_name;
    
    /** @var string
        configuration file */
    protected $config_file;
    
    /** @var string
        environment */
    protected $environment;
    
    /**
     * Constructor
     * 
     * @param string/file xml
     * @throws ConfiguratorException
     */
    public function XMLConfigurator($stream, $env) {
        $xmlelement= simplexml_load_file($stream);
        foreach($xmlelement->environment as $e) {
            if ($e['name']==$env) {
                $this->sxe= $e;
                break;
            }
        }
        if ($this->sxe === NULL) {
            throw new ConfiguratorException('Cannot find environment: ' . $env . ' in ' . $stream);
        }
        $this->application_name= $xmlelement['name'];
        $this->config_file = $stream;
        $this->environment = $env;
    }

    public function getEnvName() {
        return trim((string)$this->environment);
    }
    
    public function getApplicationName() {
        return trim((string)$this->application_name);
    }
    
    public function getApplicationPath() {
        return trim((string)$this->sxe->properties->path);
    }
    
    public function getWebContext() {
        return $this->sxe->web;
    }
    
    public function getConfigFile() {
        return $this->config_file;
    }
    
    /**
     * Configuration Example:
     * <code>
     *   <database default="foo">
     *     <dsn id="one"
     *          phptype  = "mysql"
     *          hostspec = "localhost"
     *          database = "baz"
     *          username = "root"
     *          password = "zzz" />
     *     <dsn id = "foo"
     *          phptype  = "pgsql"
     *          hostspec = "192.18.1.1"
     *          database ="test"
     *          username ="antonescu"
     *          password ="x-creeme" />
     *   </database>
     * </code>
     * @see IConfigurator::getDatabaseDsn()
     */
    public function getDatabaseDsn($id = FALSE) {
        if (!$id) $id = $this->sxe->database['default'];
        foreach( $this->sxe->database->dsn as  $dsn ) {
            if( trim($dsn['id']) == trim($id) ){
                return array (
                        'phptype'  => (string)trim($dsn['phptype']),
                        'hostspec' => (string)trim($dsn['hostspec']),
                        'username' => (string)trim($dsn['username']),
                        'password' => (string)trim($dsn['password']),
                        'database' => (string)trim($dsn['database'])
                );
            }
        }
        throw new ConfiguratorException('Database Id ' . $id . ' not found!');
    }    
    
    /**
     * Configuration example:
     * <code>
     *   <logger>
     *     <outputters>
     *       <outputter name="file" level="0">
     *         <property name="path" value="/wwwroot/whereto.log" />
     *       </outputter>
     *       <outputter name="stdout" level="0" />
     *       <outputter name="mail" level="3">
     *         <property name="subject" value="[Uh-Ah]Fatality on my server!" />
     *         <property name="address" value="user@example.com" />
     *       </outputter>
     *     </outputters>
     *   </logger>
     * </code>
     * @see IConfigurator::getLoggerOutputters()
     * @return array
     */
    public function getLoggerOutputters() {
        $i=0; $ret= array();
        if (is_null($this->sxe->logger->outputters)) return $ret;
        foreach ($this->sxe->logger->outputters->outputter as $outputter) {
            $ret[$i]['name']    = (string)trim($outputter['name']);
            $ret[$i]['level']   = (string)trim($outputter['level']);
            foreach ($outputter->property as $property) {
                $ret[$i]['properties'][(string)trim($property['name'])]= (string)trim($property['value']);
            }
            $i++;
        }
        return $ret;

    }

    /** @see IConfigurator::getLoggerFormatter() */
    public function getLoggerFormatter() {
        return ucfirst((string)trim($this->sxe->logger->formatter) . 'Formatter');
    }

    /**
     * Dinamically sets a proprety on runtime.
     *
     * Assuming that we have
     * <code>
     *   <property name="application_path" value="/wwwroot/htdocs/locknet7/app" />
     * </code>  
     * To change the value of application_path property:
     * <code>
     *   $config->setProperty('application_path', 'C:\\Fast\\www\\medick\\app');
     * </code>
     *
     * @param string, name, the name of the property.
     * @param string, value, the value of the property.
     * @throws ConfiguratorException if the property that we want to set don't exists in the xml file/string
     */
    public function setProperty($name, $value) {
        $xp = new domxpath($dom = $this->toDom());
        $property = $xp->query("//application/property[@name=\"" . $name . "\"]");
        if ($property->length != 1) {
            throw new ConfiguratorException('Cannot set the property name: ' . $name .
                'Property don\'t exist or there are two propreties with the same name');
        }
        $property->item(0)->setAttribute('value', $value);
        // save the new xml tree
        $this->sxe = simplexml_import_dom($dom, 'SimpleXMLIterator');
    }

    /**
     * Dinamically sets the logger formatter
     *
     * Note: this method is used only in unittests.
     * @param string, formatter, the formatter to use for logger
     */
    public function setLoggerFormatter($formatter) {
        $this->sxe->logger->formatter = $formatter;
    }

    /**
     * Convert this document from SXE to DOM
     * 
     * @return DomDocument
     */
    public function toDom() {
        $dom_sxe = dom_import_simplexml($this->sxe);
        $dom = new DomDocument();
        $dom_sxe = $dom->importNode($dom_sxe, true);
        $dom_sxe = $dom->appendChild($dom_sxe);
        return $dom;
    }

    /** @return the string representation of this object */
    public function toString() {
        return $this->sxe->asXML();
    }
}
