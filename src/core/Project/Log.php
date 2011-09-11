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
 * This class handles all information logging for a project.
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
class Project_Log extends Framework_DatabaseObjectAbstract
{
  protected $_id;           // the build's incremental ID
  protected $_date;         // the build's date
  protected $_type;         //
  protected $_message;      // a user generated description text (prior or after the build triggered).
  protected $_username;     // The username that triggered the log entry

  protected $_ptrProject;

  public function __construct(Project $project)
  {
    parent::__construct();
    $this->_ptrProject = $project;
    $this->_id = null;
    $this->_date = null;
    $this->_type = '';
    $this->_message = '';
    $this->_username = '';
  }

  public function __destruct()
  {
    parent::__destruct();
  }

	/**
   * Overriding the base class method, to get rid of the ptr attributes
   */
  protected function _getCurrentSignature(array $exclusions = array())
  {
    return parent::_getCurrentSignature(array('_ptrProject'));
  }

  public function init() {}

  protected function _save($force = false)
  {
    if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
    }

    if (!Database::beginTransaction()) {
      return false;
    }
    $sql = 'INSERT INTO projectlog' . $this->getPtrProject()->getId()
         . ' (type, message, username)'
         . ' VALUES (?,?,?)';
    $val = array(
      $this->getType(),
      $this->getMessage(),
      $this->getUsername(),
    );
    if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
      return false;
    }
    $this->setId($id);

    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The project log might not have been saved. [PID={$this->getPtrProject()->getId()}]", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved project log. [PID={$this->getPtrProject()->getId()}]", __METHOD__);
    #endif
    $this->resetSignature();
    return true;
  }

  static public function getList(Project $project, User $user, $access = Access::READ, array $options = array())
  {
    isset($options['sort'])?:$options['sort']=Sort::DATE_DESC;
    isset($options['pageStart'])?:$options['pageStart']=0;
    isset($options['pageLength'])?:$options['pageLength']=CINTIENT_BUILDS_PAGE_LENGTH;

    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pl.*'
         . ' FROM projectlog' . $project->getId() . ' pl, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    if ($options['sort'] != Sort::NONE) {
      $sql .= ' ORDER BY';
      switch ($options['sort']) {
        case Sort::DATE_ASC:
          $sql .= ' pl.id ASC';
          break;
        case Sort::DATE_DESC:
          $sql .= ' pl.id DESC';
      }
    }
    $sql .= ' LIMIT ?, ?';
    $val = array($project->getId(), $user->getId(), $access, $options['pageStart'], $options['pageLength']);
    if ($rs = Database::query($sql, $val)) {
      $ret = array();
      while ($rs->nextRow()) {
        $projectLog = self::_getObject($rs, $project);
        $ret[] = $projectLog;
      }
    }
    return $ret;
  }

  static private function _getObject(Resultset $rs, Project $project)
  {
    $ret = new Project_Log($project);
    $ret->setId($rs->getId());
    $ret->setDate($rs->getDate());
    $ret->setType($rs->getType());
    $ret->setMessage($rs->getMessage());
    $ret->setUsername($rs->getUsername());
    $ret->resetSignature();
    return $ret;
  }

  static public function install(Project $project)
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS projectlog{$project->getId()} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  type TINYINT DEFAULT 0,
  message TEXT DEFAULT '',
  username VARCHAR(20) NOT NULL DEFAULT ''
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems creating table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    return true;
  }

  static public function uninstall(Project $project)
  {
    $sql = "DROP TABLE projectlog{$project->getId()}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project log table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    return true;
  }
}