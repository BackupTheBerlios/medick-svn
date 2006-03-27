<?php
/**
 * @desc: Testing Route Requirements 
 */ 
class ReqController extends ApplicationController {
    
    protected $use_layout= 'main';
    
    /**
     * @desc: see conf/testor.routes.php
     */ 
    public function index() {
        if (!isset($this->params['number'])) {
            $this->flash('notice', 'redirected');
            return $this->redirect_to(NULL, 'req', array(rand()));
        }
        $this->number= $this->request->getParameter('number');
    }

}

