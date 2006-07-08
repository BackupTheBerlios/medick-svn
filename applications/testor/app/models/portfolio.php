<?php
 /**
  * This class is part of testor project
  *
  * @package testor.models
  * $Id$
  */
class Portfolio extends ActiveRecord {

    protected function before_save() {
        $this->name= htmlentities($this->name);
        $this->validates_presence_of('name');
        $this->validates_uniqueness_of('name');
        return TRUE;
    }

    protected function before_delete() {
        return !($this->name=='locknet.ro');
    }
    
    /**
     * Finds a Portfolio
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
        $args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }

}

