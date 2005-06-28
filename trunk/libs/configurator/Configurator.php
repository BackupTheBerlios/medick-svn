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
 * Configurator Interface.
 * Provides methods that needs to be implemented
 * @package locknet7.start
 */

interface Configurator {
    /**
     * It gets the section property
     * @param string, section, the section
     * @param string, property, the property
     * @return string, the section property
     */
    function getSectionProperty($section, $property);
}

class XMLConfigurator implements Configurator {

    /** SimpleXML Object */
    protected $sxe;

    /** This Configurator Instance */
    private static $instance = NULL;

    /**
     * Constructor.
     * @param string, xml, configuration file/string
     */
    private function __construct($xml) {
        if (is_file($xml)) $this->sxe = simplexml_load_file($xml, 'SimpleXMLIterator');
        else $this->sxe = simplexml_load_string($xml, 'SimpleXMLIterator');
        if ($this->sxe===false) throw new ConfiguratorException('Cannot read ' . $xml . '\n<br /> Bad Format!');
    }

    public static function getInstance($xml) {
        if (self::$instance === NULL) {
            self::$instance = new XMLConfigurator($xml);
        }
        return self::$instance;
    }

    /** @see Configurator::getSectionProperty() */
    public function getSectionProperty($section, $property) {
        if(!$this->sxe->$section) {
            throw new Exception("Cannot find " . $system . " section in your Configuration File: " . $this->configFile . "!",100);
        }
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

    // {{{ wrappers.
    public function getDatabaseProperty($property) {
        return $this->getSectionProperty('database',$property);
    }
    // }}}
    
    // {{{ logger
    public function getLoggerOutputters() {
        return $this->sxe->logger->outputters;
    }
    // }}}
    
}

class ConfiguratorException extends Exception {     }

/*
$o = XMLConfigurator::getInstance('libs/configurator/application.xml');

$sxe = $o->getLoggerOutputters();

for ($sxe->rewind(); $sxe->valid(); $sxe->next()) {   
    foreach($sxe->getChildren() as $outputter) {
        echo (string)trim($outputter['name']) . "\n";
        echo (string)trim($outputter['level']) . "\n";
        echo (string)trim($outputter['value']) . "\n";
        echo "----------\n";
    }
}
*/
