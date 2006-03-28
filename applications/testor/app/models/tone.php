<?php
/**
 * This class is part of testor project
 *
 * @package testor.models
 * $Id$
 */
class Tone extends ActiveRecord {

    protected function before_save() {
        $this->name= htmlentities($this->name);
        $this->validates()->presence_of('name');
        $this->validates()->uniqueness_of('name');
        return TRUE;
    }
    
    /**
     * Finds a Tone
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
         $args = func_get_args();
         return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }
}

