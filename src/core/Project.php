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
 * One special case with this class is that all data persistence handling is
 * done automatically, i.e., there's no need to call save() from an outside
 * scope. Usage is simple: new objects created from scratch, don't forget to
 * call init(); objects created from the database, no need to do anything.
 *
 * @package Project
 */
class Project extends DatabaseObjectAbstract
{
  protected $_buildLabel;              // The build label to be used in the packages' and builds' nomenclature (together with the counter)
  protected $_dateCheckedForChanges;   // Last status check on the project (not necessarily originating a build)
  protected $_dateCreation;
  protected $_description;
  protected $_id;
  protected $_releaseMajor;            // The current release major number
  protected $_releaseMinor;            // The current release minor number
  protected $_releaseCounter;          // the *last* number assigned to a successful created release package. Should be incremental
  protected $_scmCheckChangesTimeout;  // In minutes.
  protected $_scmConnectorType;        // * Always * loaded from the available modules on core/ScmConnector
  protected $_scmPassword;
  protected $_scmRemoteRepository;
  protected $_scmUsername;
  protected $_signature;               // Internal flag to control whether a save to database is required
  protected $_statsNumBuilds;          // Aggregated stats for the total number of project builds (to avoid summing ProjectBuild table)
  protected $_status;
  protected $_title;
  protected $_users;                   // An array of users and corresponding permissions, taken from projectuser table
  protected $_visits;                  // Counter of accesses, for hotness
  protected $_workDir;                 // The working dir of the project (sources, generated reports, etc)
  //
  // Builders
  //
  protected $_integrationBuilder;    // The builder used for continuous integration builds and package creation (serialized)
  protected $_deploymentBuilder;     // The builder available inside a package, for deployment (serialized)
  //
  // Options
  //
  protected $_optionPackageOnSuccess;  // Generate a release package on every successful build?

  const STATUS_UNINITIALIZED = 0;
  const STATUS_ERROR = 1;
  const STATUS_OK = 2;
  const STATUS_BUILDING = 3;
  const STATUS_MODIFIED = 4;
  const STATUS_UNBUILT = 5;

  public function __construct()
  {
    parent::__construct();
    $this->_buildLabel = '';
    $this->_description = '';
    $this->_scmCheckChangesTimeout = CINTIENT_PROJECT_CHECK_CHANGES_TIMEOUT_DEFAULT;
    $this->_scmConnectorType = SCM_DEFAULT_CONNECTOR;
    $this->_scmRemoteRepository = '';
    $this->_scmUsername = '';
    $this->_scmPassword = '';
    $this->_signature = null;
    $this->_statsNumBuilds = 0;
    $this->_status = self::STATUS_UNINITIALIZED;
    $this->_title = '';
    $this->_users = array();
    $this->_workDir = '';
    //
    // Builders
    //
    $this->_integrationBuilder = new BuilderElement_Project();
    $this->_deploymentBuilder = new BuilderElement_Project();
    //
    // Options
    //
    $this->_optionPackageOnSuccess = false;
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  public function build($force = false)
  {
    $params = array();
    $params['type'] = $this->getScmConnectorType();
    $params['remote'] = $this->getScmRemoteRepository();
    $params['local'] = $this->getScmLocalWorkingCopy();
    $params['username'] = $this->getScmUsername();
    $params['password'] = $this->getScmPassword();

    if ($this->getStatus() == self::STATUS_ERROR && !$force) {
      $this->touchDateCheckedForChanges();
      SystemEvent::raise(SystemEvent::INFO, "Project is in error state, to build you need to force. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }

    if ($this->getStatus() == self::STATUS_BUILDING) {
      SystemEvent::raise(SystemEvent::INFO, "Project is currently building, or is queued for building. [PROJECTID={$this->getId()}]", __METHOD__);
      $this->setStatus(self::STATUS_ERROR);
      return false;
    }

    if ($this->getStatus() != self::STATUS_MODIFIED) {
      //
      // Checkout required?
      //
      $this->touchDateCheckedForChanges();
      if ($this->getStatus() == self::STATUS_UNINITIALIZED ||
          $this->getStatus() == self::STATUS_UNBUILT ||
          !file_exists($this->getScmLocalWorkingCopy()))
      {
        if (!ScmConnector::checkout($params)) {
          SystemEvent::raise(SystemEvent::INFO, "Couldn't checkout sources. [PROJECTID={$this->getId()}]", __METHOD__);
          $this->setStatus(self::STATUS_UNINITIALIZED);
          return false;
        }
        $this->setStatus(self::STATUS_MODIFIED);
      } else {
        if (!ScmConnector::isModified($params)) {
          SystemEvent::raise(SystemEvent::INFO, "No modifications detected. [PROJECTID={$this->getId()}]", __METHOD__);
          if (!$force) {
            //$this->setStatus(self::STATUS_OK);
            return false;
          }
        }
      }
    }
    $this->setStatus(self::STATUS_MODIFIED);
    $rev = null; // Keep this for now, add it to the project build later.
    if (!ScmConnector::update($params, $rev)) {
      SystemEvent::raise(SystemEvent::INFO, "Couldn't update local sources. [PROJECTID={$this->getId()}]", __METHOD__);
      if (!$force) {
        $this->setStatus(self::STATUS_ERROR);
        return false;
      }
    }

    // We're now building
    $this->setStatus(self::STATUS_BUILDING);
    $this->_save(true); // We want the building status to update imediatelly

    //
    // Scm stuff done, setup a new build for the project
    //
    $build = new ProjectBuild($this);
    $build->setScmRevision($rev);
    if (!$build->init()) {
      $this->setStatus(self::STATUS_ERROR);
      SystemEvent::raise(SystemEvent::INFO, "Integration build failed. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }

    $this->setStatus(self::STATUS_OK);
    $this->incrementStatsNumBuilds();
    $this->incrementReleaseCounter();
    $build->setLabel($this->getCurrentReleaseLabel()); // make sure the project's release counter was incremented

    SystemEvent::raise(SystemEvent::INFO, "Integration build successful. [PROJECTID={$this->getId()}]", __METHOD__);
    return true;
  }

  public function incrementReleaseCounter()
  {
    $this->_releaseCounter++;
  }

  public function incrementStatsNumBuilds()
  {
    $this->_statsNumBuilds++;
  }

  public function getCurrentReleaseLabel()
  {
    return ($this->getBuildLabel() . '-' . $this->getReleaseMajor() . '.' . $this->getReleaseMinor() . '.' . $this->getReleaseCounter());
  }

  public function delete()
  {
    if (!Database::beginTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!ScmConnector::delete(array('local' => $this->getScmLocalWorkingCopy()))) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project sources. [ID={$this->getId()}] [DIR={$this->getScmLocalWorkingCopy()}]", __METHOD__);
    }
    if (!ProjectBuild::delete($this->getId())) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project build table. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!ProjectLog::delete($this->getId())) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project log table. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    $sql = "DELETE FROM project WHERE id=?";
    if (!Database::execute($sql, array($this->getId()))) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The project might not have been deleted. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Project deleted. [ID={$this->getId()}]");
    $this->setId(null);
    $this->resetSignature(); // No more saves for this project
    return true;
  }

  public function getScmLocalWorkingCopy()
  {
    return $this->getWorkDir() . 'sources/';
  }

  public function getReportsWorkingDir()
  {
    return $this->getWorkDir() . 'reports/';
  }

  /**
   * Call this at the very creation of the project, for checking out the sources
   * and initialization stuff like that.
   */
  public function init()
  {
    //
    // Create all the working directories
    //
    $this->setWorkDir(CINTIENT_PROJECTS_DIR . uniqid($this->getId(), true) . '/'); // Don't forget the trailing '/'!
    if (!mkdir($this->getWorkDir(), DEFAULT_DIR_MASK, true)) {
      $this->setWorkDir(null);
      SystemEvent::raise(SystemEvent::ERROR, "Could not create working root dir for project. [PID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!mkdir($this->getScmLocalWorkingCopy(), DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create sources dir for project. [PID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!mkdir($this->getReportsWorkingDir(), DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create reports dir for project. [PID={$this->getId()}]", __METHOD__);
      return false;
    }
    //
    // Setup the minimal integration builder setup
    //
    $echo = new BuilderElement_Task_Echo();
    $echo->setMessage("hello, world");
    $propertyProjectDir = new BuilderElement_Type_Property();
    $propertyProjectDir->setName('projectDir');
    $propertyProjectDir->setValue($this->getWorkDir());
    $propertyProjectDir->setFailOnError(false);
    $propertyProjectDir->setEditable(false);
    $propertyProjectDir->setDeletable(false);
    $propertyProjectDir->setVisible(false);
    $propertySourcesDir = new BuilderElement_Type_Property();
    $propertySourcesDir->setName('sourcesDir');
    $propertySourcesDir->setValue($this->getScmLocalWorkingCopy());
    $propertySourcesDir->setFailOnError(false);
    $propertySourcesDir->setEditable(false);
    $propertySourcesDir->setDeletable(false);
    $propertySourcesDir->setVisible(false);
    $target = new BuilderElement_Target();
    $target->setName('build');
    $target->addTask($propertyProjectDir);
    $target->addTask($propertySourcesDir);
    $target->addTask($echo);
    $this->_integrationBuilder->addTarget($target);
    $this->_integrationBuilder->setDefaultTarget($target->getName());
    //$this->_integrationBuilder->setBaseDir($this->getWorkDir());
    //
    // Save the project and take care of all database dependencies.
    //
    if (!$this->_save()) {
      return false;
    }
    if (!ProjectLog::install($this->getId())) {
      $this->delete();
      return false;
    }
    if (!ProjectBuild::install($this)) {
      $this->delete();
      return false;
    }
    //
    // SCM checkout of the project. If not possible to checkout now,
    // we'll just try at a later time.
    //
    $params = array(
      'type'     => $this->getScmConnectorType(),
      'remote'   => $this->getScmRemoteRepository(),
      'local'    => $this->getScmLocalWorkingCopy(),
      'username' => $this->getScmUsername(),
      'password' => $this->getScmPassword(),
    );
    if (!ScmConnector::checkout($params)) {
      $this->setStatus(self::STATUS_UNINITIALIZED);
      return false;
    }
    $this->setStatus(self::STATUS_UNBUILT);
    return true;
  }

  static public function install()
  {
    $access = Access::READ;
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS project(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  buildlabel TEXT,
  datecreation DATETIME DEFAULT CURRENT_TIMESTAMP,
  datecheckedforchanges DATETIME DEFAULT CURRENT_TIMESTAMP,
  deploymentbuilder TEXT,
  description TEXT DEFAULT '',
  integrationbuilder TEXT,
  releasemajor MEDIUMINT UNSIGNED DEFAULT 0,
  releaseminor MEDIUMINT UNSIGNED DEFAULT 0,
  releasecounter INT UNSIGNED DEFAULT 0,
  scmcheckchangestimeout MEDIUMINT UNSIGNED DEFAULT 30,
  scmconnectortype VARCHAR(20) DEFAULT '',
  scmpassword VARCHAR(255) DEFAULT '',
  scmremoterepository VARCHAR(255) DEFAULT '',
  scmusername VARCHAR(255) DEFAULT '',
  statsnumbuilds INTEGER UNSIGNED DEFAULT 0,
  status TINYINT UNSIGNED DEFAULT 0,
  title VARCHAR(255) DEFAULT '',
  visits INTEGER UNSIGNED DEFAULT 0,
  workdir VARCHAR(255) DEFAULT ''
);
CREATE TABLE IF NOT EXISTS projectuser(
  projectid INTEGER UNSIGNED NOT NULL,
  userid INTEGER UNSIGNED NOT NULL,
  access TINYINT UNSIGNED NOT NULL DEFAULT {$access},
  PRIMARY KEY (projectid, userid)
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not create Project related tables.", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Created Project related tables.", __METHOD__);
      return true;
    }
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
    $serializedIntegrationBuilder = str_replace("\0", CINTIENT_NULL_BYTE_TOKEN, serialize($this->getIntegrationBuilder()));
    $serializedDeploymentBuilder = str_replace("\0", CINTIENT_NULL_BYTE_TOKEN, serialize($this->getDeploymentBuilder()));
    $sql = 'REPLACE INTO project'
         . ' (id,datecreation,'
         . ' description,title,visits,integrationbuilder,deploymentbuilder,status,'
         . ' buildlabel,statsnumbuilds,scmpassword,scmusername,workdir,'
         . ' scmremoterepository,scmconnectortype,scmcheckchangestimeout,'
         . ' datecheckedforchanges)'
         . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $val = array(
      $this->getId(),
      $this->getDateCreation(),
      $this->getDescription(),
      $this->getTitle(),
      $this->getVisits(),
      /*SQLite3::escapeString*/($serializedIntegrationBuilder),
      /*SQLite3::escapeString*/($serializedDeploymentBuilder),
      $this->getStatus(),
      $this->getBuildLabel(),
      $this->getStatsNumBuilds(),
      $this->getScmPassword(),
      $this->getScmUsername(),
      $this->getWorkDir(),
      $this->getScmRemoteRepository(),
      $this->getScmConnectorType(),
      $this->getScmCheckChangesTimeout(),
      $this->getDateCheckedForChanges(),
    );
    if ($this->_id === null) {
      if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving project to db.", __METHOD__);
        return false;
      }
      $this->setId($id);
    } else {
      if (!Database::execute($sql, $val)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving project to db.", __METHOD__);
        return false;
      }
    }

    $sql = 'DELETE FROM projectuser WHERE projectid=' . $this->getId();
    if (!Database::execute($sql)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving project to db.", __METHOD__);
      return false;
    }
    $sql = 'REPLACE INTO projectuser'
         . ' (projectid,userid,access)'
         . ' VALUES (?,?,?)';
    if (empty($this->_users) || !is_array($this->_users)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "No users available. Problems saving project to db.", __METHOD__);
      return false;
    }
    foreach ($this->_users as $pair) {
      $val = array(
        $this->getId(),
        $pair[0],
        $pair[1],
      );
      if (!Database::insert($sql, $val)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving project to db.", __METHOD__);
        return false;
      }
    }

    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The project might not have been saved. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved project. [PID={$this->getId()}] [TITLE={$this->getTitle()}]", __METHOD__);
    #endif
    $this->resetSignature();
    return true;
  }

  public function addToUsers(array $pair)
  {
    $this->_users = array_merge($this->_users, array($pair));
  }

  public function removeFromUsers(User $user)
  {
    $i = 0;
    $removed = false;
    foreach ($this->_users as $pair) {
      if ($pair[0] == $user->getId()) {
        unset($this->_users[$i]);
        $removed = true;
        break;
      }
      $i++;
    }
    return $removed;
  }

  public function getAccessLevelFromUser(User $user)
  {
    foreach ($this->_users as $pair) {
      if ($pair[0] == $user->getId()) {
        return $pair[1];
      }
    }
    return false;
  }

  public function setAccessLevelForUser(User $user, $access)
  {
    $i = 0;
    foreach ($this->_users as $pair) {
      if ($pair[0] == $user->getId()) {
        $this->_users[$i][1] = $access;
        return true;
      }
      $i++;
    }
    return false;
  }

  public function loadUsers()
  {
    $ret = array();
    $sql = "SELECT * FROM projectuser WHERE projectid=?";
    if ($rs = Database::query($sql, array($this->getId()))) {
      while ($rs->nextRow()) {
        $ret[] = array($rs->getUserId(), $rs->getAccess());
      }
    }
    $this->setUsers($ret);
  }

  /**
   * Logs an event to the project log.
   *
   * @param string $msg
   */
  public function log($msg)
  {
    $projectLog = new ProjectLog($this->getId());
    $projectLog->setType(0);
    $projectLog->setMessage($msg);
  }

  public function touchDateCheckedForChanges()
  {
    $this->_dateCheckedForChanges = date('Y-m-d H:i:s');
  }

  /**
   * Checks if a given user has at least the specified $accessLevel.
   *
   * @param int $accessLevel
   *
   * @return bool
   */
  public function userHasAccessLevel(User $user, $accessLevel)
  {
    $hasAccessLevel = false;
    foreach ($this->_users as $userPair) {
      if ($user->getId() == $userPair[0]) {
        $hasAccessLevel = ($userPair[1] >= $accessLevel);
        break;
      }
    }
    SystemEvent::raise(SystemEvent::DEBUG, "User " . ($hasAccessLevel?'has':"doesn't have") . " access. [USER={$user->getUsername()}] [ACCESS={$accessLevel}] [PROJECTACCESSLEVEL={$userPair[1]}]", __METHOD__);
    return $hasAccessLevel;
  }

  static public function getById(User $user, $id, $access = Access::READ, array $options = array())
  {
    isset($options['loadUsers'])?:$options['loadUsers']=true;

    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $id = (int)$id;
    $sql = 'SELECT p.*'
         . ' FROM project p, projectuser pu'
         . ' WHERE p.id=?'
         . ' AND p.id=pu.projectid'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    $val = array($id, $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $ret = null;
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $options);
      }
    }
    return $ret;
  }

  static public function &getList(User $user, $access = Access::READ, array $options = array())
  {
    isset($options['sort'])?:$options['sort']=Sort::ALPHA_ASC;

    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT p.*'
         . ' FROM project p, projectuser pu'
         . ' WHERE p.id=pu.projectid'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    if ($options['sort'] != Sort::NONE) {
      $sql .= ' ORDER BY';
      switch ($options['sort']) {
        case Sort::ALPHA_ASC:
          $sql .= ' title ASC';
          break;
        case Sort::ALPHA_DESC:
          $sql .= ' title DESC';
      }
    }
    $val = array($user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $ret = array();
      while ($rs->nextRow()) {
        $project = self::_getObject($rs, $options);
        $ret[] = $project;
      }
    }
    return $ret;
  }

  static public function getCountTotalBuilds()
  {
    $ret = 0;
    $sql = 'SELECT SUM(statsnumbuilds) AS c'
         . ' FROM project p';
    if ($rs = Database::query($sql)) {
      if ($rs->nextRow()) {
        $ret = (int)$rs->getC();
      }
    }
    return $ret;
  }

  static public function getNextToBuild()
  {
    $ret = null;
    $sql = 'SELECT * FROM project p'
         . " WHERE datecheckedforchanges < DATETIME('now', -(scmcheckchangestimeout*60) || ' seconds')"
         . ' ORDER BY datecheckedforchanges ASC LIMIT 1';
    if ($rs = Database::query($sql)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs);
      }
    }
    return $ret;
  }

  /**
   *
   * @param unknown_type $rs
   */
  static private function _getObject(Resultset $rs, Array $options = array())
  {
    isset($options['loadUsers'])?:$options['loadUsers']=true;
    $ret = new Project();
    $ret->setScmConnectorType($rs->getScmConnectorType());
    $ret->setScmRemoteRepository($rs->getScmRemoteRepository());
    $ret->setScmUsername($rs->getScmUsername());
    $ret->setScmPassword($rs->getScmPassword());
    $ret->setScmCheckChangesTimeout($rs->getScmCheckChangesTimeout());
    $ret->setWorkDir($rs->getWorkDir());
    $ret->setBuildLabel($rs->getBuildLabel());
    $ret->setDateCreation($rs->getDateCreation());
    $ret->setDateCheckedForChanges($rs->getDateCheckedForChanges());
    $ret->setDescription($rs->getDescription());
    $ret->setId($rs->getId());
    $ret->setReleaseMajor($rs->getReleaseMajor());
    $ret->setReleaseMinor($rs->getReleaseMinor());
    $ret->setReleaseCounter($rs->getReleaseCounter());
    $ret->setStatsNumBuilds($rs->getStatsNumBuilds());
    $ret->setStatus($rs->getStatus());
    $ret->setTitle($rs->getTitle());
    $ret->setVisits($rs->getVisits());
    //
    // Builders
    //
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
    $unsafeSerializedIntegrationBuilder = str_replace(CINTIENT_NULL_BYTE_TOKEN, "\0", $rs->getIntegrationBuilder());
    if (($integrationBuilder = unserialize($unsafeSerializedIntegrationBuilder)) === false) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't unserialize integration builder for this project [PID={$ret->getId()}]");
      $integrationBuilder = new BuilderElement_Project();
    }
    $ret->setIntegrationBuilder($integrationBuilder);

    $unsafeSerializedDeploymentBuilder = str_replace(CINTIENT_NULL_BYTE_TOKEN, "\0", $rs->getDeploymentBuilder());
    if (($deploymentBuilder = unserialize($unsafeSerializedDeploymentBuilder)) === false) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't unserialize deployment builder for this project [PID={$ret->getId()}]");
      $deploymentBuilder = new BuilderElement_Project();
    }
    $ret->setDeploymentBuilder($deploymentBuilder);

    if ($options['loadUsers']) {
      $ret->loadUsers();
    }
    $ret->resetSignature();
    return $ret;
  }
}