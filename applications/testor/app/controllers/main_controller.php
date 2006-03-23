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
        $this->logger->debug('Classes:' . $filename);
        include_once($filename);
        $c= explode(DIRECTORY_SEPARATOR, $filename);
        $cend= end($c);
        $f= explode('_', $cend);
        $class_name= ucfirst($f[0]) . 'Controller';
        $class= new ReflectionClass($class_name);
        if ($class->isSubclassOf(new ReflectionClass('ApplicationController'))) {
          // $this->logger->debug($class);
          $ctrl[$key] = $class;
          // $this->logger->debug($ctrl[$key]);
        }
    }
    // $this->logger->debug($ctrl);
    $this->ctrl= $ctrl;
    
  }
  
}

