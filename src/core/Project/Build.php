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
  protected $_id;            // the build's incremental ID
  protected $_date;          // the build's start date
  protected $_duration;      // the build's duration (if any)
  protected $_label;         // the label on the build, also used to name the release package file
  protected $_description;   // a user generated description text (prior or after the build triggered).
  protected $_output;        // the integration builder's output collected
  protected $_status;        // indicates: failure | no_release | release
  protected $_scmRevision;   // The corresponding SCM revision on the remote repository
  protected $_specialTasks;  // Array with the build's class names of the integration builder elements that are special tasks

  protected $_ptrProject;

  const STATUS_FAIL = 0;
  const STATUS_OK_WITHOUT_PACKAGE = 1;
  const STATUS_OK_WITH_PACKAGE = 2;

  public function __construct(Project $project)
  {
    parent::__construct();
    $this->_id = null;
    $this->_date = null;
    $this->_duration = null;
    $this->_label = '';
    $this->_description = '';
    $this->_output = '';
    $this->_specialTasks = $project->getSpecialTasks();
    $this->_status = self::STATUS_FAIL;
    $this->_scmRevision = '';

    $this->_ptrProject = $project;
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
    // Get the ID, first and foremost. Also this is the start datetime
    //
    $this->setDate($this->getNowDatetime());
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
    $builderProcess = new Framework_Process($GLOBALS['settings'][SystemSettings::EXECUTABLE_PHP]);
    $builderProcess->setStdin($builderCode);
    $builderProcess->run();
    $this->setDuration(time()-strtotime($this->getDate())); // Setup the first finish time. If we get to the end of this method, update again at the end
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
    $GLOBALS['result']['ok'] = false;
    foreach ($output as $line) {
      $neck = strpos($line, '=');
      $key = substr($line, 0, $neck);
      $value = substr($line, $neck+1);
      $value = str_replace(CINTIENT_NEWLINE_TOKEN, PHP_EOL, $value);
      $GLOBALS['result'][$key] = $value;
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

    $this->setDuration(time()-strtotime($this->getDate()));

    //
    // Create this build's report dir, backing up an existing one, just in case
    //
    if (is_dir($this->getBuildDir())) {
      $backupOldBuildDir = rtrim($this->getBuildDir(), '/') . '_old_' . uniqid() . '/';
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
          SystemEvent::raise(SystemEvent::ERROR, "Unexisting special task. [PID={$project->getId()}] [BUILD={$this->getId()}] [TASK={$task}]", __METHOD__);
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
    $this->setDuration(time()-strtotime($this->getDate())); // Final duration time refresh
    return true;
  }

  public function generateReleasePackage()
  {
    $ret = false;
    if ($this->getStatus() != self::STATUS_FAIL) {
      $project = $this->getPtrProject(); // Easier handling

      $filename = "{$project->getReleasesDir()}{$project->getBuildLabel()}-{$this->getId()}";

      $command = str_replace(array('%archive', '%sources'), array($filename, $project->getScmLocalWorkingCopy()), $GLOBALS['settings'][SystemSettings::EXECUTABLE_ARCHIVER]);
      SystemEvent::raise(SystemEvent::DEBUG, "Generating release package for build. [BUILD={$this->getId()}] [PID={$project->getId()}] [COMMAND={$command}]", __METHOD__);
      $proc = new Framework_Process($command);
      $proc->run();

      $ret = true;
    }
    return $ret;
  }

  public function isOk()
  {
    return $this->_status != self::STATUS_FAIL;
  }

  public function setScmRevision($rev)
  {
    // Trying to counter the fact that very rarely the revision number
    // on an SCM connector can not be fetched... (no clue why, yet)
    if (empty($rev)) {
      $rev = '---';
    }
    $this->_scmRevision = $rev;
  }

  protected function _save($force = false)
  {
    if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
    }

    $sql = 'REPLACE INTO projectbuild' . $this->getPtrProject()->getId()
         . ' (id, label, description, output, specialtasks, status, scmrevision, date, duration)'
         . ' VALUES (?,?,?,?,?,?,?,?,?)';
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
      $this->getDate(),
      $this->getDuration(),
    );
    if ($this->_id === null) {
      if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
      $this->setId($id);
    } else {
      if (!Database::execute($sql, $val)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
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

  static public function getStats(Project $project, User $user, $access = Access::READ)
  {
    $ret = array();

    //
    // Build outcomes
    //
    $sql = 'SELECT status, COUNT(pb.status) AS c'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' GROUP BY status';
    $val = array($project->getId(), $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $r = array(0, 0);
      while ($rs->nextRow()) {
        $i = (int)$rs->getStatus();
        if ($i != self::STATUS_FAIL) {
          $i = 1;
        }
        $r[$i] += (int)$rs->getC();
      }
      $ret['buildOutcomes'] = $r;
    }

    //
    // Build timeline and duration
    //
    $ret['buildTimeline'] = array();
    $ret['buildDuration'] = array();
    $sql = 'SELECT id, status, date, duration'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    $val = array($project->getId(), $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $ok = array();
      $failed = array();
      while ($rs->nextRow()) {
        $date = strtotime($rs->getDate());
        $ret['buildDuration'][] = array($rs->getId(), $rs->getDuration());
        if ($rs->getStatus() != self::STATUS_FAIL) {
          $ok[] = $date;
        } else {
          $failed[] = $date;
        }
      }
      $ret['buildTimeline']['ok'] = $ok;
      $ret['buildTimeline']['failed'] = $failed;
    }

    //
    // Quality metrics (PHP_Depend)
    //
    $tables = Database::getTables();
    if (!empty($tables["phpdepend{$project->getId()}"])) {
      $ret['qualityTrend'] = Build_SpecialTask_PhpDepend::getTrend($project, $user, $access);
    }
    return $ret;
  }

  static private function _getObject(Resultset $rs, Project $project)
  {
    $ret = new Project_Build($project);
    $ret->setId($rs->getId());
    $ret->setDate($rs->getDate());
    $ret->setDuration($rs->getDuration());
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
    $tableName = "projectbuild{$project->getId()}";
    SystemEvent::raise(SystemEvent::INFO, "Creating $tableName related tables...", __METHOD__);

    $sql = <<<EOT
DROP TABLE IF EXISTS {$tableName}NEW;
CREATE TABLE IF NOT EXISTS {$tableName}NEW (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATETIME DEFAULT NULL,
  duration SMALLINT DEFAULT 1,
  label VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  output TEXT NOT NULL DEFAULT '',
  releasenumber VARCHAR(20) NOT NULL DEFAULT '',
  specialtasks TEXT NOT NULL DEFAULT '',
  status TINYINT UNSIGNED NOT NULL DEFAULT 0,
  scmrevision VARCHAR(40) NOT NULL DEFAULT ''
);
EOT;
    if (!Database::setupTable($tableName, $sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up $tableName table.", __METHOD__);
      return false;
    }

    // MAJOR TODO: have Project_Build automatically call each special task's install()
    //
    // Install PhpDepend schema
    //
    if (!Build_SpecialTask_PhpDepend::install($project)) {
      return false;
    }

    SystemEvent::raise(SystemEvent::INFO, "{$tableName} related tables created.", __METHOD__);
    return true;
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