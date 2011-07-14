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
 * This class will handle all build related stuff. Every single build,
 * be it successful or not, will correspond to an instance of this class.
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
class Project_Build extends Framework_DatabaseObjectAbstract
{
  protected $_id;           // the build's incremental ID
  protected $_date;         // the build's date
  protected $_label;        // the label on the build, also used to name the release package file
  protected $_description;  // a user generated description text (prior or after the build triggered).
  protected $_output;       // the integration builder's output collected
  protected $_status;       // indicates: failure | no_release | release
  protected $_scmRevision;  // The corresponding SCM revision on the remote repository
  protected $_specialTasks; // Array with the build's class names of the integration builder elements that are special tasks

  protected $_ptrProject;

  const STATUS_FAIL = 0;
  const STATUS_OK_WITHOUT_PACKAGE = 1;
  const STATUS_OK_WITH_PACKAGE = 2;

  public function __construct(Project $project)
  {
    parent::__construct();
    $this->_id = null;
    $this->_date = null;
    $this->_label = '';
    $this->_description = '';
    $this->_output = '';
    $this->_specialTasks = $project->getSpecialTasks();
    $this->_status = self::STATUS_FAIL;
    $this->_scmRevision = null;

    $this->_ptrProject = $project;
  }

  public function __destruct()
  {
    parent::__destruct();
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
    return md5(serialize($arr));
  }

  public function addToOutput($output)
  {
    $this->_output .= $output;
  }

  public function getBuildDir()
  {
    return $this->getPtrProject()->getReportsWorkingDir() . $this->getId() . '/';
  }

  public function getProjectId()
  {
    return $this->getPtrProject()->getId();
  }

  public function init()
  {
    //
    // Get the ID, first and foremost
    //
    if (!$this->_save(true)) {
      return false;
    }

    $project = $this->getPtrProject(); // Easier handling

    //
    // A few more sanity checks
    //
    $integrationBuilder = $project->getIntegrationBuilder();
    if (!($integrationBuilder instanceof Build_BuilderElement_Project)) {
      SystemEvent::raise(SystemEvent::DEBUG, "No valid integration builder specified. [PROJECTID={$this->getProjectId()}]", __METHOD__);
      return false;
    }
    if ($integrationBuilder->isEmpty()) {
      SystemEvent::raise(SystemEvent::DEBUG, "Empty integration builder. [PROJECTID={$this->getProjectId()}]", __METHOD__);
      return false;
    }

    //
    // Check for special tasks and run pre build actions
    //
    $specialTasks = $this->getSpecialTasks();
    if (!empty($specialTasks)) {
      foreach ($specialTasks as $task) {
        if (!class_exists($task)) {
          SystemEvent::raise(SystemEvent::ERROR, "Unexisting special task. [PID={$project->getId()}] [BUILD={$this->getId()}] [TASK={$task}]", __METHOD__);
          return false;
        }
        $o = new $task($this);
        if (!$o->preBuild()) {
          SystemEvent::raise(SystemEvent::ERROR, "Special task's pre build execution aborted. [PID={$project->getId()}] [BUILD={$this->getId()}] [TASK={$task}]", __METHOD__);
          return false;
        }
      }
    }

    //
    // Export and execute the builder's source code
    //
    $builderCode = $integrationBuilder->toPhp();
    SystemEvent::raise(SystemEvent::DEBUG, "Integration builder source code:" . PHP_EOL . print_r($builderCode, true), __METHOD__);
    // Execute as an external process in order to have a clean sandboxed
    // environment. eval($builderCode) is no more.
    $builderProcess = new Framework_Process();
    $builderProcess->setStdin($builderCode);
    $builderProcess->run();
    //
    // Import back into Cintient space the external builder's output
    // TODO: we should probably have this somewhere better than
    // $GLOBALS['result']...
    //
    // Also check BuilderElement_Project for the expected
    // $GLOBALS['result'] vars...
    //
    $output = explode(PHP_EOL, $builderProcess->getStdout());
    $GLOBALS['result'] = array();
    foreach ($output as $line) {
      $neck = strpos($line, '=');
      $key = substr($line, 0, $neck);
      $value = substr($line, $neck+1);
      $value = str_replace(CINTIENT_NEWLINE_TOKEN, PHP_EOL, $value);
      $GLOBALS['result'][$key] = $value;
    }
    if (empty($GLOBALS['result']['ok'])) {
      $GLOBALS['result']['ok'] = false;
    }
    if (!empty($GLOBALS['result']['stacktrace'])) {
      $this->addToOutput($GLOBALS['result']['stacktrace']);
    }
    if (!$builderProcess->emptyStderr()) {
      $this->addToOutput($builderProcess->getStderr());
    }
    if ($GLOBALS['result']['ok'] != true) {
      if (!empty($GLOBALS['result']['task'])) {
        SystemEvent::raise(SystemEvent::INFO, "Failed executing task {$GLOBALS['result']['task']}.", __METHOD__);
      } else {
        SystemEvent::raise(SystemEvent::INFO, "Failed for unknown reasons.", __METHOD__);
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Possible stacktrace: " . print_r($GLOBALS['result'], true), __METHOD__);
      return false;
    }

    //
    // Create this build's report dir, backing up an existing one, just in case
    //
    if (is_dir($this->getBuildDir())) {
      $backupOldBuildDir = $this->getBuildDir() . '_old_' . uniqid() . '/';
      if (!@rename($this->getBuildDir(), $backupOldBuildDir)) {
        SystemEvent::raise(SystemEvent::ERROR, "Couldn't create backup of existing build dir found. [PID={$project->getId()}] [DIR={$this->getBuildDir()}] [BUILD={$this->getId()}]", __METHOD__);
        return false;
      }
    }
    if (!@mkdir($this->getBuildDir(), DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't create build dir. [PID={$project->getId()}] [DIR={$this->getBuildDir()}] [BUILD={$this->getId()}]", __METHOD__);
      return false;
    }

    //
    // Run post build actions
    //
    reset($specialTasks);
    $result = true;
    if (!empty($specialTasks)) {
      foreach ($specialTasks as $task) {
        if (!class_exists($task)) {
          SystemEvent::raise(SystemEvent::ERROR, "Unexisting complex task. [PID={$project->getId()}] [BUILD={$this->getId()}] [TASK={$task}]", __METHOD__);
          continue;
        }
        $o = new $task($this);
        $result = $result & $o->postBuild();
      }
    }
    if (!$result) { // Don't abort, since this is just the post build actions, not the build itself.
      SystemEvent::raise(SystemEvent::ERROR, "Special task's post build execution had problems. [PID={$project->getId()}] [BUILD={$this->getId()}] [TASK={$task}]", __METHOD__);
    }
    $this->setStatus(self::STATUS_OK_WITHOUT_PACKAGE);
    return true;
  }

  public function isOk()
  {
    return $this->_status != self::STATUS_FAIL;
  }

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
    $sql = 'REPLACE INTO projectbuild' . $this->getPtrProject()->getId()
         . ' (id, label, description, output, specialtasks, status, scmrevision)'
         . ' VALUES (?,?,?,?,?,?,?)';
    $specialTasks = @serialize($this->getSpecialTasks());
    if ($specialTasks === false) {
      $specialTasks = serialize(array());
    }
    $val = array(
      $this->getId(),
      $this->getLabel(),
      $this->getDescription(),
      $this->getOutput(),
      $specialTasks,
      $this->getStatus(),
      $this->getScmRevision(),
    );
    if ($this->_id === null) {
      if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
      $this->setId($id);
    } else {
      if (!Database::execute($sql, $val)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
    }

    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The project build might not have been saved. [PID={$this->getPtrProject()->getId()}]", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved project build. [PID={$this->getPtrProject()->getId()}]", __METHOD__);
    #endif
    $this->resetSignature();
    return true;
  }

  static public function getById($buildId, Project $project, User $user, $access = Access::READ, array $options = array())
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $buildId = (int)$buildId;
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' AND pb.id=?';
    $val = array($project->getId(), $user->getId(), $access, $buildId);
    if ($rs = Database::query($sql, $val)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $project);
      }
    }
    return $ret;
  }

  static public function getLatest(Project $project, User $user, $access = Access::READ, array $options = array())
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' ORDER BY pb.id DESC'
         . ' LIMIT 1';
    $val = array($project->getId(), $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $project);
      }
    }
    return $ret;
  }

  static public function getList(Project $project, User $user, $access = Access::READ, array $options = array())
  {
    isset($options['sort'])?:$options['sort']=Sort::DATE_DESC;
    isset($options['pageStart'])?:$options['pageStart']=0;
    isset($options['pageLength'])?:$options['pageLength']=CINTIENT_BUILDS_PAGE_LENGTH;

    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    if ($options['sort'] != Sort::NONE) {
      $sql .= ' ORDER BY';
      switch ($options['sort']) {
        case Sort::DATE_ASC:
          $sql .= ' pb.id ASC';
          break;
        case Sort::DATE_DESC:
          $sql .= ' pb.id DESC';
      }
    }
    $sql .= ' LIMIT ?, ?';
    $val = array($project->getId(), $user->getId(), $access, $options['pageStart'], $options['pageLength']);
    if ($rs = Database::query($sql, $val)) {
      $ret = array();
      while ($rs->nextRow()) {
        $projectBuild = self::_getObject($rs, $project);
        $ret[] = $projectBuild;
      }
    }
    return $ret;
  }

  static private function _getObject(Resultset $rs, Project $project)
  {
    $ret = new Project_Build($project);
    $ret->setId($rs->getId());
    $ret->setDate($rs->getDate());
    $ret->setLabel($rs->getLabel());
    $ret->setDescription($rs->getDescription());
    $ret->setOutput($rs->getOutput());
    $specialTasks = @unserialize($rs->getSpecialTasks());
    if ($specialTasks === false) {
      $specialTasks = array();
    }
    $ret->setSpecialTasks($specialTasks);
    $ret->setStatus($rs->getStatus());
    $ret->setScmRevision($rs->getScmRevision());
    //
    // Get all extras related to this build
    //
    //$ret->setPtrPhpDepend(PhpDepend::getById($ret, $GLOBALS['user'], Access::READ));

    $ret->resetSignature();
    return $ret;
  }

  static public function install(Project $project)
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS projectbuild{$project->getId()} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  label VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  output TEXT NOT NULL DEFAULT '',
  specialtasks TEXT NOT NULL DEFAULT '',
  status TINYINT UNSIGNED NOT NULL DEFAULT 0,
  scmrevision INTEGER UNSIGNED NOT NULL DEFAULT 0
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems creating table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    //
    // Install PhpDepend schema
    //
    return Build_SpecialTask_PhpDepend::install($project);
  }

  static public function uninstall(Project $project)
  {
    $sql = "DROP TABLE projectbuild{$project->getId()}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project build table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    return true;
  }
}