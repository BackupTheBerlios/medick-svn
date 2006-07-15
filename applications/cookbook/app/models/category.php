<?php
/**
 * This class is part of cookbook project
 *
 * @package cookbook.models
 * $Id: generator.php 398 2006-05-23 19:18:28Z aurelian $
 */
class Category extends ActiveRecord {

    protected $has_many= 'recipes';
    
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
