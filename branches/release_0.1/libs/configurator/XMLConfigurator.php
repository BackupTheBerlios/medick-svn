<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005,2006 Oancea Aurelian <aurelian@locknet.ro>
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

include_once('configurator/IConfigurator.php');

/**
 * XML file-based Configurator
 * 
 * @package medick.configurator
 * @author Oancea Aurelian
 */

class XMLConfigurator extends Object implements IConfigurator {

    /** @var SimpleXML */
    protected $sxe;

    /**
     * Constructor
     * 
     * @param string/file xml
     * @throws ConfiguratorException
     */
    public function XMLConfigurator($xml) {
        if (file_exists($xml)) {
            if (is_readable($xml)) {
                $this->sxe = simplexml_load_file($xml, 'SimpleXMLIterator');
            } else {
                throw new IOException("Cannot read: " . $xml . " Permission deny");
            }
        } else {
            $this->sxe = @simplexml_load_string($xml, 'SimpleXMLIterator');
        }
        if ($this->sxe === false) {
            throw new ConfiguratorException("Cannot read: " . $xml . " Bad Format!");
        }
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
        throw new ConfiguratorException('Database Id ' . $id . 'not found!');
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

    /** @see IConfigurator::getProperty() */
    public function getProperty($name) {
        foreach($this->sxe->property as  $properties ) {
            if($properties['name'] != $name)
                continue;
            $_query= (string)trim($properties['value']);
            if(
                ($_query=='') ||
                (strtolower($_query) == 'false') ||
                (strtolower($_query)=='off') ||
                ($_query == '0')
            ) {
                return FALSE;
            } elseif(
                (strtolower($_query)=='true') ||
                (strtolower($_query)=='on') ||
                ($_query == '1')
            ) {
                return TRUE;
            } else {
                return (string)$_query;
            }
        }
        throw new ConfiguratorException('Property ' . $name . ' not found!');
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
     * Note: This method is used only in unittests.
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
