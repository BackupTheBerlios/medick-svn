lo<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005, 2006 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Oancea Aurelian nor the names of his contributors may
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

include_once('action/controller/session/ISessionContainer.php');

/**
 * Creole Session Container.
 * 
 * It requires a Database table defined like this:
 * <code>
 * CREATE TABLE c_session (
 *	`session_id` VARCHAR (255) PRIMARY KEY,
 *	`session_data` TEXT,
 *	`session_lastmodified` DATETIME
 * );
 * </code>
 * 
 * <b>Warning:</b> this class was tested only with mysql.
 *  
 * @package medick.action.controller
 * @subpackage session
 * @author Oancea Aurelian 
 */
class CreoleSessionContainer extends Object implements ISessionContainer {

	/** @var CreoleConnection */
	protected $conn=null;
	
	/** @var Logger */
	protected $logger= null;
	
	/**
	 * Constructor
	 */
	public function CreoleSessionContainer() {
		$this->conn = Creole::getConnection(Registry::get('__configurator')->getDatabaseDsn());
		$this->logger= Registry::get('__logger');
	}

	public function open($save_path, $session_name) {
		$this->gc();
		return true;
	}
	
	public function close() {
	    $this->conn->close();
		return true;
	}

	public function read($id) {
        $timeout = time() - ini_get('session.gc_maxlifetime');
		$stmt = $this->conn->prepareStatement('SELECT session_data FROM c_session   
											   WHERE session_id = ? AND session_lastmodified > ' . $timeout);
		$stmt->setString(1, $id);

		try {
			$rs = $stmt->executeQuery();
			
			if($rs->getRecordCount()==0) {
				return '';
			}
			$rs->first();
			return $rs->get('session_data');
			
		} catch (SQLException $sqlEx) {
            $this->logger->debug($sqlEx->getMessage());
			return false;
		}
	}


	public function write($id,$session_data){
		// 1. select
		$stmt = $this->conn->prepareStatement('SELECT session_id FROM c_session WHERE session_id=?');
		$stmt->setString(1,$id);
		
		try{
			$rs = $stmt->executeQuery();
			// 2. count results:
			if($rs->getRecordCount()==1) {
				// 3. update:
				$stmt = $this->conn->prepareStatement('UPDATE c_session SET session_data=?,' . 
													  ' session_lastmodified=now() WHERE session_id=?');
				$stmt->setString(1,$session_data);
				$stmt->setString(2,$id);

				try{
					$rs=$stmt->executeUpdate();
					return true;
				} catch (SQLException $sqlEx){
					$this->logger->debug($sqlEx->getMessage());
					return false;
				}
				
			} else {
				// 4. insert
				$stmt = $this->conn->prepareStatement('INSERT INTO c_session (session_id,session_data,session_lastmodified) VALUES (?,?,now())');
				$stmt->setString(1,$id);
				$stmt->setString(2,$session_data);
				try{
					$rs = $stmt->executeUpdate();
					return true;
				} catch (SQLException $sqlEx){
					$this->logger->debug($sqlEx->getMessage());
					return false;
				}
			}
		} catch (SQLException $sqlEx){
			$this->logger->debug($sqlEx->getMessage());
			return false;
		}
		

	}

	public function destroy($id) {
        $stmt = $this->conn->prepareStatement('DELETE FROM c_session WHERE session_id = ?');
        $stmt->setString(1,$id);
        try {
			$rs = $stmt->executeUpdate();
			return true;
		} catch (SQLException $sqlEx) {
            $this->logger->debug($sqlEx->getMessage());
			return false;
		}
	}

	/**
	 * Garbage Collection
	 *
	 * @param maxlifetime, after that the session will expire
	 * @return boolean
	 */
	public function gc($maxlifetime=300) {
//		if($maxlifetime==''){
//			$c = LocknetConfig::singleton();
//			$maxlifetime = $c->getSessionProperty('sess_time');
//			if(!$maxlifetime){
//				$maxlifetime = 0;
//			}
//		}
		
        $stmt = $this->conn->prepareStatement(
			'DELETE FROM c_session WHERE `session_lastmodified` < ?');
		$stmt->setTimestamp(1,time()-$maxlifetime);
        try {
			$rs = $stmt->executeUpdate();
			return true;
		} catch (SQLException $sqlEx) {
            $this->logger->debug($sqlEx->getMessage());
			return false;
		}
	}
	
	
}
