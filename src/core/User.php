<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca
 *
 *  This file is part of Cintient.
 *
 *  Cintient is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Cintient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Cintient. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @package User
 */
class User
{
  private $_avatar;          // The avatar's location
  private $_cos;                // The user's classes of service
  private $_creationDate;
  private $_email;              // Registration unique email
  private $_enabled;            // 0 for disabled or >0 for enabled
  private $_id;
  private $_name;               // Real name
  private $_notificationEmails; // Email addresses for notification purposes
  private $_signature;
  private $_username;

  private $_projectId;          // The active project ID
  private $_projectAccess;      // The active project access level

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
    $this->_avatar = null;
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
         . ' (id,avatar,cos,creationdate,email,enabled,name,'
         . 'notificationEmails,username)'
         . ' VALUES (?,?,?,?,?,?,?,?,?)';
    $val = array(
      $this->getId(),
      $this->getAvatar(),
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

  /**
   * Use this instead of getCos() directly, in order to be more future
   * proof. It's possible that the User's CoS will someday be an array,
   * so using this should keep you safe.
   *
   * @param UserCos $cos
   */
  public function hasCos($cos)
  {
    return ($this->getCos() == $cos);
  }

  public function getAccess()
  {
    if ($this->getCos()) {
    }
  }

  public function getAvatarUrl()
  {
    if (($pos = strpos($this->getAvatar(), 'local:')) === 0) {
      return UrlManager::getForAsset(substr($this->getAvatar(), 6), array('avatar' => 1));
    } elseif (($pos = strpos($this->getAvatar(), 'gravatar:')) === 0) {
      // TODO: Maybe it's best to keep a user's gravatar email a hash it on-the-fly
      // instead of storing the definitive URL to it.
      return substr($this->getAvatar(), 9);
    } else {
      return '/imgs/anon_avatar_50.png';
    }
  }

  public function setAvatarLocal($filename)
  {
    $this->_avatar = 'local:' . $filename;
  }

  public function setAvatarGravatar($id)
  {
    $this->_avatar = 'gravatar:' . $id;
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

  static public function getListByIncompleteTerm($term)
  {
    $ret = array();
    $sql = 'SELECT * FROM user '
         . " WHERE username LIKE %?%"
         . " OR name LIKE %?%";
    $rs = Database::query($sql, array($term, $term));
    while ($rs->nextRow()) {
      $ret[] = self::_getObject($rs);
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
  notificationemails TEXT NOT NULL DEFAULT '',
  avatar VARCHAR(255) NOT NULL DEFAULT ''
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
    $ret->setAvatar($rs->getAvatar());
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