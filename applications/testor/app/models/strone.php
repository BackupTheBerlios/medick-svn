<?php
/**
 * This class is part of testor project
 *
 * @package testor.models
 * $Id$
 */
class Strone extends ActiveRecord {

    /**
     * Finds a Strone
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
    	$args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
	}

}

