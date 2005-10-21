<?php

// $Id$    

include_once('active/record/Base.php');

class Book extends ActiveRecordBase {

    protected $has_one= array('author');

}
