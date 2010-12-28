<?php
/*
 * Cintient, Continuous Integration made simple.
 * 
 * Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */

/**
 * 
 */
class User
{
  private $_cos; // The user's classes of service
  private $_creationDate;
  private $_email; // Registration unique email
  private $_enabled; // 0 for disabled or >0 for enabled
  private $_id;
  private $_name; // Real name
  private $_notificationEmails; // Email addresses for notification purposes
  private $_signature;
  private $_username;
  
  private $_projectId; // The active project ID
  private $_projectAccess; // The active project access level
  
  /**
   * Magic method implementation for calling vanilla getters and setters. This
   * is rigged to work only with private/protected non-static class variables
   * whose nomenclature follows the Zend Coding Standard.
   * 
   * @param $name
   * @param $args
   */
  public function __call($name, $args)
  {
    if (strpos($name, 'get') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      return $this->$var;
    } elseif (strpos($name, 'set') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      $this->$var = $args[0];
      return true;
    }
    return false;
  }
  
  public function __construct()
  {
    $this->_cos = UserCos::USER;
    $this->_creationDate = date('Y-m-d H:i:s');
    $this->_email = '';
    $this->_enabled = true;
    $this->_id = null;
    $this->_name = '';
    $this->_notificationEmails = '';
    $this->_signature = null;
    $this->_username = '';
  }
  
  public function __destruct()
  {
    $this->_save();
  }
  
  private function _save($force=false)
  {
    if ($this->_getCurrentSignature() == $this->_signature && !$force) {
      SystemEvent::raise(SystemEvent::DEBUG, "User save called, but no saving is required.", __METHOD__);
      return false;
    }
    $sql = 'REPLACE INTO user'
         . ' (id,cos,creationdate,email,enabled,name,notificationEmails,username)'
         . ' VALUES (?,?,?,?,?,?,?,?)';
    $val = array(
      $this->getId(),
      $this->getCos(),
      $this->getCreationDate(),
      $this->getEmail(),
      $this->getEnabled(),
      $this->getName(),
      $this->getNotificationEmails(),
      $this->getUsername(),
    );
    if ($this->_id === null) {
      if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems inserting user.", __METHOD__);
        return false;
      }
      $this->setId($id);
    } else {
      if (!Database::execute($sql, $val)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems updating user.", __METHOD__);
        return false;
      }
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Saved user. [USERNAME={$this->getUsername()}]", __METHOD__);
    $this->updateSignature();
    return true;
  }
  
  /**
   * Call this at the very creation of the object, whenever not loading it from
   * the database.
   */
  public function init()
  {
    return $this->_save();
  }
  
  public function getAccess()
  {
    if ($this->getCos()) {
    }
  }
  
  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    return md5(serialize($arr));
  }

  /**
   * Call this only after there is a user ID available.
   */
  public function setPassword($password)
  {
    if (empty($this->_id)) {
      return false;
    }
    $password = hash('sha256', PASSWORD_SALT . $password);
    $sql = 'REPLACE INTO userauth (userid, password) VALUES (?,?)';
    $val = array($this->getId(), $password);
    if (!Database::execute($sql, $val)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems updating db.", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Updated password for user. [USERNAME={$this->getUsername()}]", __METHOD__);
    #endif
    return true;
  }
  
  public function updateSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }
  
  static public function getById($id)
  {
    $ret = null;
    $sql = 'SELECT * FROM user WHERE id=?';
    $rs = Database::query($sql, array($id));
    if ($rs->nextRow()) {
      $ret = self::_getObject($rs);
    }
    return $ret;
  }
  
  static public function getByUsername($username)
  {
    $ret = null;
    $sql = 'SELECT * FROM user WHERE username=?';
    $rs = Database::query($sql, array($username));
    if ($rs->nextRow()) {
      $ret = self::_getObject($rs);
    }
    return $ret;
  }
  
  static public function install()
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS user(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(20),
  enabled TINYINT UNSIGNED NOT NULL DEFAULT 1,
  creationdate DATETIME DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(255),
  cos TINYINT UNSIGNED,
  name VARCHAR(255) NOT NULL DEFAULT '',
  notificationemails TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS userauth(
  userid INTEGER PRIMARY KEY,
  password VARCHAR(255)
);
EOT;
    SystemEvent::raise(SystemEvent::INFO, "Creating User related tables.", __METHOD__);
    return Database::execute($sql);
  }
  
  /**
   * 
   * @param unknown_type $rs
   */
  static private function _getObject($rs)
  {
    $ret = new User();
    $ret->setCos($rs->getCos());
    $ret->setCreationDate($rs->getCreationDate());
    $ret->setEmail($rs->getEmail());
    $ret->setEnabled($rs->getEnabled());
    $ret->setId($rs->getId());
    $ret->setName($rs->getName());
    $ret->setNotificationEmails($rs->getNotificationEmails());
    $ret->setUsername($rs->getUsername());
    $ret->updateSignature();
    return $ret;
  }
}