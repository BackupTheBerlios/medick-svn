<?php
/**
 * This class is part of testor project
 *
 * @package testor.models
 * $Id$
 */
class Manager extends ActiveRecord {

    /**
     * Finds a Manager
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
        $args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }

}

