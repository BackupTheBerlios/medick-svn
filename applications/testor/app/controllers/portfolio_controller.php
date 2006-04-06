<?php
/**
 * 
 * @desc: Test for: <ul><li>FormHelper::check_box</li><li>uniqueness_of validator</li><li>ActiveRecordHelper::error_message_on</li></ul>
 * @package testor.controller
 * $Id$
 */ 
class PortfolioController extends ApplicationController {
    
    protected $use_layout= 'main';
    
    protected $models= 'portfolio';
    
    /** 
     * @desc: it lists all <em>portfolios</em> 
     */
    public function index() {
        $this->portfolios= Portfolio::find();
    }
    
    /** 
     * @desc: prints the form for adding a new <em>portfolio</em>, it submits to <a href="portfolio/create">create</a>
     */
    public function add() {
        $this->portfolio= new Portfolio();
    }
    
    /** 
     * @desc: creates a new <em>portfolio</em>
     */
    public function create() {
        $this->portfolio= new Portfolio($this->request->getParameter('portfolio'));
        $this->set_status($this->portfolio);

        if ($this->portfolio->save() === FALSE) {
            $this->render('add');
        } else {
            $this->flash('notice', '<em>' . $this->portfolio->name . '</em> created');
            $this->redirect_to('index');
        }
    }
    
    /** 
     * @desc: prints the form for editing a <em>portfolio</em>, it submits to <a href="portfolio/update">update</a>
     */
    public function edit() {
        try {
            $this->portfolio= Portfolio::find($this->request->getParameter('id'));
        } catch (RecordNotFoundException $rnfEx) {
            $this->flash('error', $rnfEx->getMessage());
            $this->redirect_to('index');
        }
    }

    /** 
     * @desc: updates a <em>portfolio</em>
     */
    public function update() {
        try {
            $this->portfolio= Portfolio::find($this->request->getParameter('id'))->attributes($this->request->getParameter('portfolio'));
            $this->set_status($this->portfolio);
            if ($this->portfolio->save() === FALSE) {
                $this->render('edit');
            } else {
                $this->flash('notice', $this->portfolio->name . ' updated');
                $this->redirect_to('index');
            }
        } catch (ActiveRecordException $arEx) {
            $this->logger->warn($arEx->getMessage());   
            $this->flash('error', $arEx->getMessage());
            $this->redirect_to('index');
        }
    }

    private function set_status($portfolio) {
        if (!isset($this->params['portfolio']['status'])) {
          $portfolio->status=0;
        } else {
          $portfolio->status=1;
        }
    }
    
    /**
     * @desc: removes a <em>portfolio</em>
     */
    public function delete() {
        try {
            $portfolio= Portfolio::find($this->request->getParameter('id'));
            if ($portfolio->delete()===FALSE) {
                $this->flash('error', 'Cannot delete <em>' . $portfolio->name . '</em>!');
                return $this->redirect_to('index');
            }
            $this->flash('notice', '<em>' . $portfolio->name . '</em> succesfully removed');
            $this->redirect_to('index');
        } catch (ActiveRecordException $arEx) {
            $this->flash('error', $arEx->getMessage());
            $this->redirect_to('index');
        }
    }

}

