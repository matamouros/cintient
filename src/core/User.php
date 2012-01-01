<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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
 *
 * @package     User
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class User extends Framework_DatabaseObjectAbstract
{
  protected $_avatar;         // The avatar's location
  protected $_cos;            // The user's classes of service
  protected $_creationDate;
  protected $_email;          // Registration unique email
  protected $_enabled;        // 0 for disabled or >0 for enabled
  protected $_id;
  protected $_name;           // Real name
  protected $_notifications;  // Global notifications settings for the user
  protected $_signature;
  protected $_username;

  protected $_projectId;      // The active project ID
  protected $_projectAccess;  // The active project access level

  public function __construct()
  {
    parent::__construct();
    $this->_avatar = null;
    $this->_cos = UserCos::USER;
    $this->_creationDate = date('Y-m-d H:i:s');
    $this->_email = '';
    $this->_enabled = true;
    $this->_id = null;
    $this->_name = '';
    $this->_notifications = array();
    $this->_username = '';
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  protected function _save($force = false)
  {
  if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
    }

    //
    // The following is a workaround on the fact that the translation of this
    // serialized object to the database gets all broken, due to the fact of PHP
    // introducing NULL bytes around the '*' that is prepended before protected
    // variable members, in the serialized mode. This method replaces those
    // problematic NULL bytes with an identifier string '~~NULL_BYTE~~',
    // rendering serialization and unserialization of these specific kinds of
    // object safe. Credits to travis@travishegner.com on:
    // http://pt.php.net/manual/en/function.serialize.php#96504
    //
    $serializedNotifications = str_replace("\0", CINTIENT_NULL_BYTE_TOKEN, serialize($this->getNotifications()));

    $sql = 'REPLACE INTO user'
         . ' (id,avatar,cos,creationdate,email,enabled,name,'
         . 'notifications,username)'
         . ' VALUES (?,?,?,?,?,?,?,?,?)';
    $val = array(
      $this->getId(),
      $this->getAvatar(),
      $this->getCos(),
      $this->getCreationDate(),
      $this->getEmail(),
      $this->getEnabled(),
      $this->getName(),
      $serializedNotifications,
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
    $this->resetSignature();
    return true;
  }

  /**
   * Call this at the very creation of the object, whenever not loading
   * it from the database.
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
      return 'imgs/anon_avatar_50.png';
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

  /**
   * Not only a getter for the notifications attribute, it always tries
   * to make sure that this attribute always has the latest notification
   * handlers, even if they have not been configured/seen by the user b4.
   */
  public function getNotifications()
  {
    $baseline = array();
    foreach ($this->_notifications as $handler) {
      $baseline[] = get_class($handler);
    }
    $availableHandlers = NotificationSettings::getHandlers();
    // If new handlers are not available here, create them.
    foreach ($availableHandlers as $handler) {
      if (!in_array($handler, $baseline)) {
        // Create an empty notification instance
        $notification = new $handler();
        $this->_notifications[$handler] = $notification;
      }
    }
    // If existing handlers are not available anymore, remove them
    foreach ($this->_notifications as $handler => $_) {
      if (!in_array($handler, $availableHandlers)) {
        unset($this->_notifications[$handler]);
      }
    }
    return $this->_notifications;
  }

  /**
   * Gets a notification handler
   */
  public function getActiveNotificationHandler($handler)
  {
    return (!empty($this->_notifications[$handler])?$this->_notifications[$handler]:false);
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
    SystemEvent::raise(SystemEvent::INFO, "Creating user related tables...", __METHOD__);

    //
    // USER
    //
    $tableName = 'user';
    $sql = <<<EOT
DROP TABLE IF EXISTS {$tableName}NEW;
CREATE TABLE IF NOT EXISTS {$tableName}NEW(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(20),
  enabled TINYINT UNSIGNED NOT NULL DEFAULT 1,
  creationdate DATETIME DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(255),
  cos TINYINT UNSIGNED,
  name VARCHAR(255) NOT NULL DEFAULT '',
  notifications TEXT NOT NULL DEFAULT '',
  avatar VARCHAR(255) NOT NULL DEFAULT ''
);
EOT;
    if (!Database::setupTable($tableName, $sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up $tableName table.", __METHOD__);
      return false;
    }

    //
    // USERAUTH
    //
    $tableName = 'userauth';
    $sql = <<<EOT
DROP TABLE IF EXISTS {$tableName}NEW;
CREATE TABLE IF NOT EXISTS {$tableName}NEW(
  userid INTEGER PRIMARY KEY,
  password VARCHAR(255)
);
EOT;
    if (!Database::setupTable($tableName, $sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up $tableName table.", __METHOD__);
      return false;
    }

    SystemEvent::raise(SystemEvent::INFO, "User related tables created.", __METHOD__);
    return true;
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
    $ret->setUsername($rs->getUsername());
    //
    // The following is a workaround on the fact that the translation of this
    // serialized object to the database gets all broken, due to the fact of PHP
    // introducing NULL bytes around the '*' that is prepended before protected
    // variable members, in the serialized mode. This method replaces those
    // problematic NULL bytes with an identifier string '~~NULL_BYTE~~',
    // rendering serialization and unserialization of these specific kinds of
    // object safe. Credits to travis@travishegner.com on:
    // http://pt.php.net/manual/en/function.serialize.php#96504
    //
    $unsafeSerializedNotifications = str_replace(CINTIENT_NULL_BYTE_TOKEN, "\0", $rs->getNotifications());
    if (($notifications = unserialize($unsafeSerializedNotifications)) === false) {
    $notifications = array();
    }
    $ret->setNotifications($notifications);
    $ret->resetSignature();
    return $ret;
  }
}