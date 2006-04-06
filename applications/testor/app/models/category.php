<?php
/**
 * This class is part of testor project
 *
 * @package testor.models
 * $Id$
 */
class Category extends ActiveRecord {

    /**
     * Finds a Category
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
        $args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }

}

