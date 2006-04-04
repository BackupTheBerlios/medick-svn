<?php
/**
 * $Id$
 * @desc: tests cookies behavior
 */ 
class CookieController extends ApplicationController {
    
    protected $use_layout= 'main';
    
    /**
     * @desc: list all the cookies and print a form to <a href="cookie/add">add</a> a new one, the user can also remove a cookie
     */ 
    public function index() {
        $this->template->assign('cookies', $this->request->getCookies());
    }

    /**
     * @desc: process the form that adds a new cookie
     */ 
    public function add() {
        $values= $this->request->getParameter('cookie');
        // manual check.
        if ( $values === NULL ||
            (isset($values['name']) && $values['name'] == '') || 
            (isset($values['value']) && $values['value'] == '')
        ) {
            return $this->sendError($values, 'Cannot set cookie, one of cookie name or value is missing');
        }
        $cookie= new Cookie($values['name'], $values['value']);
        // $cookie->setPath($values['path']);
        // $cookie->setExpire((int)$values['expire']);
        // $cookie->setDomain($values['domain']);
        // $cookie->setSecure($values['secure']);
        $this->response->setCookie($cookie);
        $this->flash('notice', 'Added Cookie <em>' . $cookie->getName() . '</em>');
        $this->redirect_to('index');
    }
    
    /**
     * @desc: removes a cookie
     */ 
    public function nuke() {
        if ($this->request->getParameter('name') === $this->session->getName()) {
            $this->flash('error', 'Cookie: <b>' . $this->session->getName() . '</b> 
                                   cannot be removed since this is your Session Name');
        } else {
            // a cookie can be distroyed by setting the value to FALSE
            $this->response->setCookie(new Cookie($this->request->getParameter('name'), FALSE));
            $this->flash('notice', 'Cookie: <em>' . $this->request->getParameter('name') . '</em> was removed.<br />
                                    <b>Close/Open</b> your browser to see the change.');
        }
        $this->redirect_to('index');
    }

    private function sendError($cookie_values, $reason) {
        $this->template->assign('cookie_values', $cookie_values);
        $this->flash('error', $reason);
        $this->redirect_to('index');
    }

}

