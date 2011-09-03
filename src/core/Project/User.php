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
 * This class helps maintain the users associated with a given project,
 * as well as their access permissions and notification options.
 *
 * @package     Project
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Project_User extends Framework_DatabaseObjectAbstract
{
  protected $_access;
  protected $_notifications; // A NotificationSettings object

  protected $_ptrProject;
  protected $_ptrUser;

  public function __construct(Project $project, User $user, $access = Access::DEFAULT_USER_ACCESS_LEVEL_TO_PROJECT)
  {
    parent::__construct();
    $this->_ptrProject = $project;
    $this->_ptrUser = $user;
    $this->_access = $access;
    $this->_notifications = new NotificationSettings();
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  static public function deleteByProject(Project $project)
  {
    $sql = "DELETE FROM projectuser WHERE projectid=?";
    return Database::execute($sql, array($project->getId()));
  }

  public function fireNotification($event, $msg, $params = array())
  {
    if (empty($params['title'])) {
      $params['title'] = $this->_ptrProject->getTitle();
    }
    if (empty($params['uri'])) {
      $params['uri'] = '';
    }
    // TODO: this whole method's content should be a full fledged operational
    // class for dealing with notifications.
    foreach ($this->_notifications->getSettings() as $handlerClass => $settings) {
      if (!empty($settings[$event]) &&
          ($handler = $this->_ptrUser->getActiveNotificationHandler($handlerClass)) !== false &&
          !$handler->isEmpty())
      {
        if (!$handler->fire($msg, $params)) {
          $msg = "Problems notifying user {$this->_ptrUser->getUsername()} using handler '" . $handlerClass . "'.";
          SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
          $this->_ptrProject->log($msg);
        } else {
          SystemEvent::raise(SystemEvent::DEBUG, "Notification sent through $handlerClass, on event $event.", __METHOD__);
        }
      } else {
        SystemEvent::raise(SystemEvent::DEBUG, "Notification not sent through $handlerClass.", __METHOD__);
      }
    }
  }

  public function getAccessLevel()
  {

  }

  /**
   * Overriding the base class method, to get rid of the ptr attributes
   */
  protected function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    $arr['_ptrProject'] = null;
    unset($arr['_ptrProject']);
    $arr['_ptrUser'] = null;
    unset($arr['_ptrUser']);
    return md5(serialize($arr));
  }

  /**
   * Utility getter
   */
  public function getProjectId()
  {
    return $this->_ptrProject->getId();
  }

  /**
   * Utility getter
   */
  public function getUserId()
  {
    return $this->_ptrUser->getId();
  }

  public function init()
  {}

  /**
   * Database setup
   */
  static public function install()
  {
    $access = Access::READ;
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS projectuser(
  projectid INTEGER UNSIGNED NOT NULL,
  userid INTEGER UNSIGNED NOT NULL,
  access TINYINT UNSIGNED NOT NULL DEFAULT {$access},
  notifications TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (projectid, userid)
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not create Project_User related tables.", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Created Project_User related tables.", __METHOD__);
      return true;
    }
  }

  /**
   * A public save interface is required...
   *
   * @param bool $force Indicates whether to force saving, despite of
   * the fact that no changes were detected.
   */
  public function save($force = true)
  {
    return $this->_save($force);
  }

  /**
   * This method is always to be called from within Project::_save(),
   * and thus it assumes a rolling transaction. That caller should handle
   * all transactioning logic.
   */
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

    $sql = 'REPLACE INTO projectuser'
    . ' (projectid,userid,access,notifications)'
    . ' VALUES (?,?,?,?)';
    $val = array(
      $this->getProjectId(),
      $this->getUserId(),
      $this->getAccess(),
      $serializedNotifications,
    );
    if (!Database::insert($sql, $val)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving project user to db.", __METHOD__);
      return false;
    }

    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved project user. [PID={$this->getProjectId()}] [USER={$this->getUserId()}]", __METHOD__);
    #endif
    $this->resetSignature();
    return true;
  }

  /**
   * Gets a specific Project_User.
   *
   * @param Project $project
   * @param User $user
   * @param array $options
   */
  static public function getByUser(Project $project, User $user, array $options = array())
  {
    $ret = null;
    $sql = "SELECT * FROM projectuser WHERE projectid=? AND userid=?";
    if (($rs = Database::query($sql, array($project->getId(), $user->getId()))) && $rs->nextRow()) {
      $ret = self::_getObject($rs, array('project' => $project, 'user' => $user));
    }
    return $ret;
  }

  /**
   * Gets a list of fully instantiated Project_User objects, which are
   * all the users of this specific project.
   *
   * @param Project $project The project to fetch the users from
   * @param array $options
   */
  static public function &getList(Project $project, array $options = array())
  {
    $ret = array();
    $sql = "SELECT * FROM projectuser WHERE projectid=?";
    if ($rs = Database::query($sql, array($project->getId()))) {
      while ($rs->nextRow()) {
        $user = User::getById($rs->getUserId());
        if (!$user instanceof User) {
          SystemEvent::raise(SystemEvent::ERROR, "User ID references a non-existing user.",__METHOD__);
        } else {
          if (($projectUser = self::_getObject($rs, array('project' => $project, 'user' => $user))) === false) {
            SystemEvent::raise(SystemEvent::ERROR, "User is required for creating a new " . __CLASS__, __METHOD__);
            continue;
          }
          $ret[] = $projectUser;
        }
      }
    }
    return $ret;
  }

  static private function _getObject(Resultset $rs, Array $options = array())
  {
    if (empty($options['user']) || !($options['user'] instanceof User) ||
        empty($options['project']) || !($options['project'] instanceof Project)) {
      return false;
    }
    $ret = new self($options['project'], $options['user'], $rs->getAccess());
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