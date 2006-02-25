<?php

// $Id$    

include_once('active/record/Base.php');

class News extends ActiveRecord {
  
    public function before_save() {
      $this->validates()->presence_of('body', 'title');
      $this->validates()->uniqueness_of('title');
      return TRUE;
    }
  
    public static function find() {
        $args= func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__,$args));
    }
    
}

