<?php

// $Id$

include_once('configurator/XMLConfigurator.php');

class XMLConfiguratorTest extends UnitTestCase {

    public function testCreation() {
        $this->assertNotNull(new XMLConfigurator('dummy/dummy.xml'));
        $this->assertNotNull(new XMLConfigurator('<?xml version="1.0" encoding="UTF-8"?><application></application>'));
        try {
            new XMLConfigurator('non_existant_file.xml');
            $this->fail('ConfiguratorException should be thrown!');
        } catch (Exception $e) {
            $this->assertIsA($e, 'ConfiguratorException');
        }
    }

    public function testDatabaseDsn() {
        $xml='<?xml version="1.0" encoding="UTF-8"?><application>
                <database default="two">
                    <dsn id="one"
                        phptype  = "mysql"
                        hostspec = "localhost"
                        database = "todo"
                        username = "root"
                        password = "zzz" />
                    <dsn id = "two"
                        phptype  = "pgsql"
                        hostspec = "192.18.1.1"
                        database ="test"
                        username ="antonescu"
                        password ="x-creeme" />
                </database>
            </application>';
        $config= new XMLConfigurator($xml);
        try {
            $config->getDatabaseDsn('foo');
            $this->fail('ConfiguratorException should be thrown!');
        } catch (MedickException $cEx) {
            $this->assertIsA($cEx, 'ConfiguratorException');
        }
        $dsn= $config->getDatabaseDsn();
        $this->assertEqual('pgsql', $dsn['phptype']);
        $dsn= $config->getDatabaseDsn('one');
        $this->assertEqual('mysql', $dsn['phptype']);
    }

    public function testLoggerOutputters() {
        $xml='<?xml version="1.0" encoding="UTF-8"?><application>
                <logger>
                    <outputters>
                        <outputter name="file"    level="0" value="/wwwroot/htdocs/locknet7/log/locknet7.log" />
                        <outputter name="stdout"  level="0" />
                    </outputters>
                </logger>
            </application>';
        $config= new XMLConfigurator($xml);
        $this->assertIsA($config->getLoggerOutputters(), 'SimpleXMLIterator');
        $xml='<?xml version="1.0" encoding="UTF-8"?><application><outputter name="stdout"  level="0" /></application>';
        $config= new XMLConfigurator($xml);
        $this->assertNull($config->getLoggerOutputters());
    }

    public function testLoggerFormatter() {
        $xml='<?xml version="1.0" encoding="UTF-8"?><application><logger><formatter>simple</formatter></logger></application>';
        $config= new XMLConfigurator($xml);
        $this->assertEqual('SimpleFormatter', $config->getLoggerFormatter());
    }

    public function testProperty() {
       $xml='<?xml version="1.0" encoding="UTF-8"?><application>
                <property name="one"    value="/wwwroot" />
                <property name="two"    value="on" />
                <property name="three"  value="1" />
                <property name="four"   value="TRUE" />
                <property name="five"   value="off" />
                <property name="six"    value="0" />
                <property name="seven"  value="false" />
            </application>';
        $config= new XMLConfigurator($xml);
        $this->assertEqual('/wwwroot', $config->getProperty('one'));
        $this->assertTrue($config->getProperty('two'));
        $this->assertTrue($config->getProperty('three'));
        $this->assertTrue($config->getProperty('four'));
        $this->assertFalse($config->getProperty('five'));
        $this->assertFalse($config->getProperty('six'));
        $this->assertFalse($config->getProperty('seven'));
        try {
            $config->getProperty('foo');
            $this->fail('ConfiguratorException should be thrown!');
        } catch (Exception $cEx) {
            $this->assertIsA($cEx, 'ConfiguratorException');
        }
    }

}

