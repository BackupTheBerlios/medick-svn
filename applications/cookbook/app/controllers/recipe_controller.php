<?php
class RecipeController extends ApplicationController {
    
    protected $models = array('recipe');
    
	public function index() {
        $this->recipes= Recipe::find();
    }

}
