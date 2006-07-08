<?php
/**
 * This class is part of testor project
 *
 * @package testor.models
 * $Id$
 */
class Project extends ActiveRecord {

    protected $belongs_to= 'portfolio';
    protected $has_one   = 'manager';
    protected $has_many  = 'milestones';
    protected $has_and_belongs_to_many = 'categories';
    
    /**
     * Finds a Project
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
        $args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }

}

