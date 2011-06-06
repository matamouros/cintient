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
 * @package Project
 */
class ProjectBuild extends CintientObjectAbstract
{
  protected $_id;           // the build's incremental ID
  protected $_date;         // the build's date
  protected $_label;        // the label on the build, also used to name the release package file
  protected $_description;  // a user generated description text (prior or after the build triggered).
  protected $_output;       // the integration builder's output collected
  protected $_status;       // indicates: failure | no_release | release
  protected $_signature;    // Internal flag to control whether a save to database is required
  protected $_scmRevision;  // The corresponding SCM revision on the remote repository

  protected $_ptrPhpDepend;
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
    $this->_status = self::STATUS_FAIL;
    $this->_signature = null;
    $this->_scmRevision = null;

    $this->_ptrProject = $project;
    $this->_ptrPhpDepend = null;
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  public function createReportFromJunit()
  {
    $junitReportFile = $this->getBuildDir() . CINTIENT_JUNIT_REPORT_FILENAME;
    if (!is_file($junitReportFile)) {
      SystemEvent::raise(SystemEvent::ERROR, "Junit file not found. [PID={$this->getPtrProject()->getId()}] [BUILD={$this->getId()}] [FILE={$junitReportFile}]", __METHOD__);
      return false;
    }
    try {
      $xml = new SimpleXMLElement($junitReportFile, 0, true);
    } catch (Exception $e) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems processing Junit XML file. [PID={$this->getPtrProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
      return false;
    }
    $xmls = $xml->children();
    foreach ($xmls as $node) {
      $imageFilename = '';
      $successes = array(); // assertions - failures
      $failures = array();
      $methodsNames = array();
      $classes = array();
      $methods = array();
      $classXml = call_user_func(function ($node) { // Access file testsuites directly (last level before testcases).
        if (isset($node->attributes()->file)) {
          return $node;
        } else {
          return f($node->children());
        }
      }, $node);
      $class = new TestClass();
      $class->setName($classXml->attributes()->name);
      $class->setFile((string)$classXml->attributes()->file);
      $class->setTests((string)$classXml->attributes()->tests);
      $class->setAssertions((string)$classXml->attributes()->assertions);
      $class->setFailures((string)$classXml->attributes()->failures);
      $class->setErrors((string)$classXml->attributes()->errors);
      $class->setTime((string)$classXml->attributes()->time);
      $class->setChartFilename(md5($this->getPtrProject()->getId() . $this->getId() . $class->getFile()) . '.png');
      //
      // After call_user_func above we're exactly at the test class (file) root level,
      // with level 1 being the unit test (method of the original class)
      // and level 2 being the various datasets used in the test (each a
      // test case).
      //
      foreach ($classXml->children() as $methodXml) {
        $method = new TestMethod();
        $method->setName($methodXml->getName());
        $method->setTests((string)$methodXml->attributes()->tests);
        $method->setAssertions((string)$methodXml->attributes()->assertions);
        $method->setFailures((string)$methodXml->attributes()->failures);
        $method->setErrors((string)$methodXml->attributes()->errors);
        $method->setTime((string)$methodXml->attributes()->time);
        $methods[] = $method;

        $time = (float)$methodXml->attributes()->time * 1000; // to milliseconds
        $methodsNames[] = $methodXml->attributes()->name;
        $f = ((((float)$methodXml->attributes()->failures) * $time) / (float)$methodXml->attributes()->assertions);
        $successes[] = (float)$time - (float)$f;
        $failures[] = $f;
      }

      $chartFile = "{$this->getBuildDir()}{$class->getChartFilename()}";
      if (!is_file($chartFile)) {
        if (!Chart::unitTests($chartFile, $methodsNames, $successes, $failures)) {
          SystemEvent::raise(SystemEvent::ERROR, "Chart file for unit tests was not saved. [PID={$this->getPtrProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
        } else {
          SystemEvent::raise(SystemEvent::INFO, "Generated chart file for unit tests. [PID={$this->getPtrProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
        }
      }
      $class->setTestMethods($methods);
      $classes[] = $class;
      return $classes;
    }
  }

  /**
   * Overriding the base class method, to get rid of the ptr attributes
   */
  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    $arr['_ptrProject'] = null;
    unset($arr['_ptrProject']);
    $arr['_ptrPhpDepend'] = null;
    unset($arr['_ptrPhpDepend']);
    return md5(serialize($arr));
  }

  public function getBuildDir()
  {
    return $this->getPtrProject()->getReportsWorkingDir() . $this->getId() . '/';
  }

  public function getJdependChartFilename()
  {
    if (!$this->getPtrPhpDepend() instanceof PhpDepend) {
      return false;
    }
    return $this->getPtrPhpDepend()->getJdependChartFilename();
  }

  public function getOverviewPyramidFilename()
  {
    if (!$this->getPtrPhpDepend() instanceof PhpDepend) {
      return false;
    }
    return $this->getPtrPhpDepend()->getOverviewPyramidFilename();
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

    $project = $this->getPtrProject(); // Easier

    //
    // A few more sanity checks
    //
    $integrationBuilder = $project->getIntegrationBuilder();
    if (!($integrationBuilder instanceof BuilderElement_Project)) {
      SystemEvent::raise(SystemEvent::DEBUG, "No valid integration builder specified. [PROJECTID={$this->getProjectId()}]", __METHOD__);
      return false;
    }
    if ($integrationBuilder->isEmpty()) {
      SystemEvent::raise(SystemEvent::DEBUG, "Empty integration builder. [PROJECTID={$this->getProjectId()}]", __METHOD__);
      return false;
    }

    //
    // Export and execute the builder's source code
    //
    $phpBuilder = $integrationBuilder->toString('cintient');
    SystemEvent::raise(SystemEvent::DEBUG, "Integration builder source code:" . PHP_EOL . print_r($phpBuilder, true), __METHOD__);
    eval ($phpBuilder); // A whole set of global vars should be set after return
    $this->setOutput(implode(PHP_EOL, $GLOBALS['result']['stacktrace']));
    if ($GLOBALS['result']['ok'] != true) {
      if (!empty($GLOBALS['result']['task'])) {
        SystemEvent::raise(SystemEvent::INFO, "Failed executing task {$GLOBALS['result']['task']}. [OUTPUT={$GLOBALS['result']['output']}]", __METHOD__);
      } else {
        SystemEvent::raise(SystemEvent::INFO, "Failed for unknown reasons. [OUTPUT={$GLOBALS['result']['output']}]", __METHOD__);
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Stacktrace: " . print_r($GLOBALS['result'], true), __METHOD__);
      return false;
    }

    // Create this build's report dir, backing up an existing one
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
    // Backup the original junit report file
    // TODO: only if unit tests were comissioned!!!!
    //
    //if (UNIT_TESTES_WERE_DONE) {
      if (!@copy($project->getReportsWorkingDir() . CINTIENT_JUNIT_REPORT_FILENAME, $this->getBuildDir() . CINTIENT_JUNIT_REPORT_FILENAME)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not backup original Junit XML file [PID={$project->getId()}] [BUILD={$this->getId()}]", __METHOD__);
      }
    //}

    // PHP_Depend related code
    //TODO: It's really necessary to only call this if PHP_Depend task is configured for the project.
    $this->_ptrPhpDepend = new PhpDepend($this);
    $this->_ptrPhpDepend->init();

    return true;
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
         . ' (id, label, description, output, status, scmrevision)'
         . ' VALUES (?,?,?,?,?,?)';
    $val = array(
      $this->getId(),
      $this->getLabel(),
      $this->getDescription(),
      $this->getOutput(),
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
    $ret = new ProjectBuild($project);
    $ret->setId($rs->getId());
    $ret->setDate($rs->getDate());
    $ret->setLabel($rs->getLabel());
    $ret->setDescription($rs->getDescription());
    $ret->setOutput($rs->getOutput());
    $ret->setStatus($rs->getStatus());
    $ret->setScmRevision($rs->getScmRevision());
    //
    // Get all extras related to this build
    //
    $ret->setPtrPhpDepend(PhpDepend::getById($ret, $GLOBALS['user'], Access::READ));

    $ret->resetSignature();
    return $ret;
  }

  static public function install(Project $project)
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS projectbuild{$project->getId()} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  label VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  output TEXT NOT NULL DEFAULT '',
  status TINYINT UNSIGNED DEFAULT 0,
  scmrevision INTEGER UNSIGNED DEFAULT 0
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems creating table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    //
    // Install PhpDepend schema
    //
    return PhpDepend::install($project);
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