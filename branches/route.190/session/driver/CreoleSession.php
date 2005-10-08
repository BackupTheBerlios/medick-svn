<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice, 
//   this list of conditions and the following disclaimer. 
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation 
//   and/or other materials provided with the distribution. 
//   * Neither the name of locknet.ro nor the names of its contributors may 
//   be used to endorse or promote products derived from this software without 
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
// 
// $Id$
// 
//////////////////////////////////////////////////////////////////////////////////
// }}}

/**
 * @package locknet7.action.controller.session.driver
 */

class CreoleSession extends Session implements ISession {

    /** DB connection */
    protected $conn=null;

    /** logger */
    protected $logger=null;

    /** config parameters */
    protected $_params;
    
    /**
     * Constructor
     */
    public function __construct($params=array()) {
        if(!isset($params['table'])) {
            $this->_params['table'] = 'fastwork_core_sessions';
        } else {
            $this->_params['table'] = $params['table'];
        }
        include_once('LocknetDatabase.php');
        $this->conn = LocknetDatabase::getInstance();

        // register this object as a session handler:
        session_set_save_handler(array($this, 'open'),
                                 array($this, 'close'),
                                 array($this, 'read'),
                                 array($this, 'write'),
                                 array($this, 'destroy'),
                                 array($this, 'gc'));
        
        if( (isset($params['autostart'])) AND ($params['autostart'] == true) ){
            session_start();
        }
        
    }

    public function open($save_path,$session_name) {
        $this->gc();
        return true;
    }
    
    /**
     * DB Disconect
     */
    public function close()
    {
        /** $this->conn = null; */
        return true;
    }

    public function read($id)
    {
        $timeout = time() - ini_get('session.gc_maxlifetime');
        $stmt = $this->conn->prepareStatement('SELECT session_data FROM ' . $this->_params['table'] . 
                                              ' WHERE session_id = ? AND session_lastmodified > ' . $timeout);
        $stmt->setString(1, $id);

        try {
            $rs = $stmt->executeQuery();

            /** echo $this->conn->lastQuery . '<br />'; */
            
            if($rs->getRecordCount()==0) {
                return '';//false;
            }
            $rs->first();
            return $rs->get("session_data");
            
        } catch (SQLException $sqlEx) {
            echo $sqlEx->getMessage();
            // TODO: log error!
            return false;
        }
    }


    public function write($id,$session_data){
        // 1. select
        $stmt = $this->conn->prepareStatement('SELECT session_id FROM ' . $this->_params['table'] . 
                                    ' WHERE session_id=?');
        $stmt->setString(1,$id);
        
        try{
            $rs = $stmt->executeQuery();
            // 2. count results:
            if($rs->getRecordCount()==1) {
                // 3. update:
                $stmt = $this->conn->prepareStatement('UPDATE ' . $this->_params['table'] . ' SET session_data=?,' . 
                                                      ' session_lastmodified=now() WHERE session_id=?');
                $stmt->setString(1,$session_data);
                $stmt->setString(2,$id);

                try{
                    $rs=$stmt->executeUpdate();
                    return true;
                } catch (SQLException $sqlEx){
                    echo $sqlEx->getMessage();
                    return false;
                }
                
            } else {
                // 4. insert
                $stmt = $this->conn->prepareStatement('INSERT INTO ' . $this->_params['table'] . 
                                        ' (session_id,session_data,session_lastmodified) VALUES (?,?,now())');
                $stmt->setString(1,$id);
                $stmt->setString(2,$session_data);
                try{
                    $rs = $stmt->executeUpdate();
                    return true;
                } catch (SQLException $sqlEx){
                    echo $sqlEx->getMessage();
                    return false;
                }
            }
        } catch (SQLException $sqlEx){
            // TODO: log error!
            echo $sqlEx->getMessage();
            return false;
        }
        

    }

    public function destroy($id) {
        $stmt = $this->conn->prepareStatement('DELETE FROM ' . $this->_params['table'] . ' WHERE session_id = ?');
        $stmt->setString(1,$id);
        try {
            $rs = $stmt->executeUpdate();
            return true;
        } catch (SQLException $sqlEx) {
            echo $sqlEx->getMessage();
            // TODO: log error!
            return false;
        }
    }

    /**
     * Garbage Collection
     *
     * @param maxlifetime, after that the session will expire
     * @return boolean
     */
    public function gc($maxlifetime='') {
        if($maxlifetime==''){
            $c = ApplicationConfigParser::getInstance();
            $maxlifetime = $c->getSessionProperty('sess_time');
            if(!$maxlifetime){
                $maxlifetime = 0;
            }
        }
        
        $stmt = $this->conn->prepareStatement(
            "DELETE FROM `" . $this->_params['table'] . "` WHERE `session_lastmodified` < ?");
        $stmt->setTimestamp(1,time()-$maxlifetime);
        try {
            $rs = $stmt->executeUpdate();
            // echo "Rows removed: " . $rs . "<br />";
            return true;
        } catch (SQLException $sqlEx) {
            echo $sqlEx->getMessage();
            // TODO: log error!
            return false;
        }
    }
}
