<?php
/**
 * 
 * @desc: Entry point for Testor Application
 * @package testor.controller
 * $Id$
 */ 
class MainController extends ApplicationController {

  protected $use_layout= 'main';
    
  /**
   * @desc: List all the available controllers / actions for this application
   */ 
  public function index() {
    $ctrl= array();
    foreach (glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . "*_controller.php") as $key=>$filename) {
        // $this->logger->debug('Classes:' . $filename);
        include_once($filename);
        $c= explode(DIRECTORY_SEPARATOR, $filename); // ==> main_controller.php
        $f= explode('_', end($c)); // ==> main
        $class_name= ucfirst($f[0]) . 'Controller'; // ==> MainController
        $class= new ReflectionClass($class_name);
        if ($class->isSubclassOf(new ReflectionClass('ApplicationController'))) {
          $ctrl[$key] = $class;
        }
    }
    $this->template->assign('ctrl',$ctrl);
  }
}

