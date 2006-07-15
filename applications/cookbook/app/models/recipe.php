<?php
/**
 * This class is part of cookbook project
 *
 * @package cookbook.models
 * $Id: generator.php 398 2006-05-23 19:18:28Z aurelian $
 */
class Recipe extends ActiveRecord {

    protected $belongs_to = 'category';
    
    /**
     * Finds a Recipe
     *
     * @see ActiveRecord::build()
     * @return mixed
     */
    public static function find() {
        $args = func_get_args();
        return ActiveRecord::build(new QueryBuilder(__CLASS__, $args));
    }
}
