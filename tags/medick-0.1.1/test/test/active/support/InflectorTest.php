<?php

// $Id$    
    
include_once('active/support/Inflector.php');
 
class InflectorTest extends UnitTestCase {

    public function testPlurals() {
        $this->assertEqual(Inflector::pluralize('category'), 'categories');
        $this->assertEqual(Inflector::pluralize('person'), 'persons');
        $this->assertEqual(Inflector::pluralize('mouse'),    'mice');
        $this->assertEqual(Inflector::pluralize('search'),   'searches');
        $this->assertEqual(Inflector::pluralize('alias'),    'aliases');
        $this->assertEqual(Inflector::pluralize('monitor'),  'monitors');
    }

    public function testSingulars() {
        $this->assertEqual(Inflector::singularize('categories'), 'category');
        $this->assertEqual(Inflector::singularize('mice'),       'mouse');
        $this->assertEqual(Inflector::singularize('searches'),   'search');
        $this->assertEqual(Inflector::singularize('years'),      'year');
        $this->assertEqual(Inflector::singularize('aliases'),    'alias');
    }
}
