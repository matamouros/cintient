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
 * @package     Project
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Project extends Framework_DatabaseObjectAbstract
{
  protected $_avatar;                  // The avatar's location
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
  protected $_specialTasks;            // Array with the current class names of the integration builder elements that are special tasks
  protected $_statsNumBuilds;          // Aggregated stats for the total number of project builds (to avoid summing Project_Build table)
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
  const STATUS_FAILED = 6;

  public function __construct()
  {
    parent::__construct();
    $this->_avatar = null;
    $this->_buildLabel = '';
    $this->_description = '';
    $this->_scmCheckChangesTimeout = CINTIENT_PROJECT_CHECK_CHANGES_TIMEOUT_DEFAULT;
    $this->_scmConnectorType = SCM_DEFAULT_CONNECTOR;
    $this->_scmRemoteRepository = '';
    $this->_scmUsername = '';
    $this->_scmPassword = '';
    $this->_specialTasks = array();
    $this->_statsNumBuilds = 0;
    $this->_status = self::STATUS_UNINITIALIZED;
    $this->_title = '';
    $this->_users = array();
    $this->_workDir = '';
    //
    // Builders
    //
    $this->_integrationBuilder = new Build_BuilderElement_Project();
    $this->_deploymentBuilder = new Build_BuilderElement_Project();
    //
    // Options
    //
    $this->_optionPackageOnSuccess = false;
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  /**
   * Receives a builder element and adds it to the project's integration
   * builder. It optionally receives an ID of the element to add to as a
   * child. It defaults to adding to the first target found in the project,
   * if no element ID is specified. This method also checks if the element
   * is a special task, and registers it accordingly in the project.
   *
   * @param Build_BuilderElement $element
   * @param string $id
   */
  public function addToIntegrationBuilder(Build_BuilderElement $element, $id = null)
  {
    //
    // TODO: implement "add to id" logic later
    //
    if (empty($id)) {
      if ($element instanceof Build_BuilderElement_Type_Property) {
        $properties = $this->getIntegrationBuilder()->getProperties();
        $properties[] = $element;
        $this->getIntegrationBuilder()->setProperties($properties);
      } else {
        $targets = $this->getIntegrationBuilder()->getTargets();
        $target = $targets[0];
        $target->addTask($element);
      }
    }
    //
    // If it's a special task, register it with the project
    //
    if ($element->isSpecialTask()) {
      $this->registerSpecialTask($element->getSpecialTask());
    }
  }

  public function resetScmConnector()
  {
    if (!Framework_Filesystem::removeDir($this->getScmLocalWorkingCopy()) && file_exists($this->getScmLocalWorkingCopy())) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not remove existing sources working copy. [PID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!mkdir($this->getScmLocalWorkingCopy(), DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not recreate sources dir for project. [PID={$this->getId()}]", __METHOD__);
      return false;
    }
    // Don't checkout the project here, or else the request might timeout
    //if (!ScmConnector::checkout($params)) {
      $this->setStatus(self::STATUS_UNINITIALIZED);
    //  return false;
    //}
    return true;
  }

	/**
   * Receives a builder element and removes it from the project's integration
   * builder. It does it's best to find the matching [deeply] nested element
   * and removes it. It also checks if the removed element is a special task,
   * and unregisters it accordingly from the project.
   *
   * @param Build_BuilderElement $element
   * @param string $id
   */
  public function removeFromIntegrationBuilder(Build_BuilderElement $element)
  {
    $newBuilder = $this->getIntegrationBuilder()->deleteElement($element->getInternalId());
    $this->setIntegrationBuilder($newBuilder);
    //
    // If it's a special task, unregister it with the project
    //
    if ($element->isSpecialTask()) {
      $this->unregisterSpecialTask($element->getSpecialTask());
    }
  }

  public function registerSpecialTask($taskName)
  {
    $this->_specialTasks[] = $taskName;
  }

  public function unregisterSpecialTask($taskName)
  {
    if (($key = array_search($taskName, $this->_specialTasks)) !== false) {
      $this->_specialTasks[$key] = null;
      unset($this->_specialTasks[$key]);
      // Little trick to reindex the array keys to avoid a hole in the key
      // sequence after removing
      $this->_specialTasks = array_slice($this->_specialTasks, 0);
    }
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
      //$this->setStatus(self::STATUS_ERROR);
      return false;
    }

    if ($this->getStatus() != self::STATUS_MODIFIED) {
      //
      // Checkout required?
      //
      $this->touchDateCheckedForChanges();
      if ($this->getStatus() == self::STATUS_UNINITIALIZED ||
          !file_exists($this->getScmLocalWorkingCopy()))
      {
        if (!ScmConnector::checkout($params)) {
          SystemEvent::raise(SystemEvent::INFO, "Couldn't checkout sources. [PROJECTID={$this->getId()}]", __METHOD__);
          $this->setStatus(self::STATUS_UNINITIALIZED);
          return false;
        }
      } else {
        if ($this->getStatus() == self::STATUS_UNBUILT) {
          $force = true;
        }
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
    $build = new Project_Build($this);
    $build->setScmRevision($rev);
    $this->triggerNotification(NotificationSettings::BUILD_STARTED);
    if (!$build->init()) {
      $this->setStatus(self::STATUS_FAILED);
      SystemEvent::raise(SystemEvent::INFO, "Integration build failed. [PROJECTID={$this->getId()}]", __METHOD__);
      $this->triggerNotification(NotificationSettings::BUILD_FAILED);
      return false;
    }

    $this->setStatus(self::STATUS_OK);
    $this->incrementStatsNumBuilds();
    $this->incrementReleaseCounter();
    $build->setLabel($this->getCurrentReleaseLabel()); // make sure the project's release counter was incremented

    SystemEvent::raise(SystemEvent::INFO, "Integration build successful. [PROJECTID={$this->getId()}]", __METHOD__);
    $this->triggerNotification(NotificationSettings::BUILD_SUCCESS);
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
    if (!Project_Build::uninstall($this)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project build table. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!Project_Log::uninstall($this)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project log table. [ID={$this->getId()}]", __METHOD__);
      return false;
    }
    if (!$this->deleteUsers()) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project user records. [ID={$this->getId()}]", __METHOD__);
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

  public function deleteUsers()
  {
    $this->_users = array();
    return Project_User::deleteByProject($this);
  }

  /**
   * Triggers a notification event, that will in turn fire a notification
   * for all registered project users.
   *
   * @param int $event The event type for the notification. @see NotificationSettings
   */
  public function triggerNotification($event)
  {
    foreach ($this->_users as $user) {
      $user->fireNotification($event);
    }
  }

  public function getScmLocalWorkingCopy()
  {
    return $this->getWorkDir() . 'sources/';
  }

  public function getReportsWorkingDir()
  {
    return $this->getWorkDir() . 'reports/';
  }

  public function getAvatarUrl()
  {
    if (($pos = strpos($this->getAvatar(), 'local:')) === 0) {
      return UrlManager::getForAsset(substr($this->getAvatar(), 6), array('avatar' => 1));
    } else {
      return 'imgs/anon_avatar_50.png';
    }
  }

  public function setAvatarLocal($filename)
  {
    $this->_avatar = 'local:' . $filename;
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
    $echo = new Build_BuilderElement_Task_Echo();
    $echo->setMessage("hello, world");
    $propertyProjectDir = new Build_BuilderElement_Type_Property();
    $propertyProjectDir->setName('projectDir');
    $propertyProjectDir->setValue($this->getWorkDir());
    $propertyProjectDir->setFailOnError(false);
    $propertyProjectDir->setEditable(false);
    $propertyProjectDir->setDeletable(false);
    $propertyProjectDir->setVisible(false);
    $propertySourcesDir = new Build_BuilderElement_Type_Property();
    $propertySourcesDir->setName('sourcesDir');
    $propertySourcesDir->setValue($this->getScmLocalWorkingCopy());
    $propertySourcesDir->setFailOnError(false);
    $propertySourcesDir->setEditable(false);
    $propertySourcesDir->setDeletable(false);
    $propertySourcesDir->setVisible(false);
    $target = new Build_BuilderElement_Target();
    $target->setName('build');
    // Following is commented out so that all tasks are added through the new addToIntegrationBuilder()
    //$target->addTask($propertyProjectDir);
    //$target->addTask($propertySourcesDir);
    //$target->addTask($echo);
    $this->_integrationBuilder->addTarget($target);
    $this->_integrationBuilder->setDefaultTarget($target->getName());
    $this->addToIntegrationBuilder($propertyProjectDir);
    $this->addToIntegrationBuilder($propertySourcesDir);
    $this->addToIntegrationBuilder($echo);
    //$this->_integrationBuilder->setBaseDir($this->getWorkDir());
    //
    // Save the project and take care of all database dependencies.
    //
    if (!$this->_save()) {
      return false;
    }
    if (!Project_Log::install($this)) {
      $this->delete();
      return false;
    }
    if (!Project_Build::install($this)) {
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
    /*if (!ScmConnector::checkout($params)) {
      $this->setStatus(self::STATUS_UNINITIALIZED);
      return false;
    }
    $this->setStatus(self::STATUS_UNBUILT);
    */
    $this->setStatus(self::STATUS_UNINITIALIZED);
    return true;
  }

  static public function install()
  {
    SystemEvent::raise(SystemEvent::INFO, "Creating project related tables...", __METHOD__);

    $tableName = 'project';
    $sql = <<<EOT
DROP TABLE IF EXISTS {$tableName}NEW;
CREATE TABLE IF NOT EXISTS {$tableName}NEW (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  buildlabel TEXT NOT NULL DEFAULT '',
  datecreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  datecheckedforchanges DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deploymentbuilder TEXT NOT NULL DEFAULT '',
  description TEXT DEFAULT '',
  integrationbuilder TEXT NOT NULL DEFAULT '',
  releasemajor MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  releaseminor MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  releasecounter INT UNSIGNED NOT NULL DEFAULT 0,
  scmcheckchangestimeout MEDIUMINT UNSIGNED NOT NULL DEFAULT 30,
  scmconnectortype VARCHAR(20) NOT NULL DEFAULT '',
  scmpassword VARCHAR(255) NOT NULL DEFAULT '',
  scmremoterepository VARCHAR(255) NOT NULL DEFAULT '',
  scmusername VARCHAR(255) NOT NULL DEFAULT '',
  specialtasks TEXT NOT NULL DEFAULT '',
  statsnumbuilds INTEGER UNSIGNED NOT NULL DEFAULT 0,
  status TINYINT UNSIGNED NOT NULL DEFAULT 0,
  title VARCHAR(255) NOT NULL DEFAULT '',
  visits INTEGER UNSIGNED NOT NULL DEFAULT 0,
  workdir VARCHAR(255) NOT NULL DEFAULT '',
  avatar VARCHAR(255) NOT NULL DEFAULT ''
);
EOT;
    if (!Database::setupTable($tableName, $sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up project table.", __METHOD__);
      return false;
    }

    if (!Project_User::install()) {
      return false;
    }

    //
    // Upgrade Project_Build tables
    //
    $tables = Database::getTables();
    $dummyProject = new Project();
    $dummyProject->setAutoSave(false); // Never save this dummy project
    foreach ($tables as $table) {
      if (preg_match('/^(projectbuild)(\d+)$/', $table, $matches)) {
        $dummyProject->setId($matches[2]);
        if (!Project_Build::install($dummyProject)) {
          return false;
        }
      } elseif (preg_match('/^(projectlog)(\d+)$/', $table, $matches)) {
        $dummyProject->setId($matches[2]);
        if (!Project_Log::install($dummyProject)) {
          return false;
        }
      }
    }
    $dummyProject = null;
    unset($dummyProject);

    SystemEvent::raise(SystemEvent::INFO, "All project related tables created.", __METHOD__);
    return true;
  }

  protected function _save($force = false)
  {
    if (!$this->_autoSave) {
      return true;
    }
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
         . ' (id,avatar,datecreation,'
         . ' description,title,visits,integrationbuilder,deploymentbuilder,status,'
         . ' buildlabel,statsnumbuilds,scmpassword,scmusername,workdir,'
         . ' scmremoterepository,scmconnectortype,scmcheckchangestimeout,'
         . ' datecheckedforchanges, specialtasks)'
         . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $specialTasks = @serialize($this->getSpecialTasks());
    if ($specialTasks === false) {
      $specialTasks = serialize(array());
    }
    $val = array(
      $this->getId(),
      $this->getAvatar(),
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
      $specialTasks,
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
    // The project users
    Project_User::deleteByProject($this); // Reset it
    foreach ($this->_users as $projectUser) {
      if (!$projectUser->save(true)) {
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

  public function addToUsers(User $user, $accessLevel = null)
  {
    $projectUser = new Project_User($this, $user, $accessLevel);
    $this->_users = array_merge($this->_users, array($projectUser));
  }

  public function removeFromUsers(User $user)
  {
    $i = 0;
    $removed = false;
    foreach ($this->_users as $projectUser) {
      if ($user->getId() == $projectUser->getUserId()) {
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
    foreach ($this->_users as $projectUser) {
      if ($user->getId() == $projectUser->getUserId()) {
        return $projectUser->getAccess();
      }
    }
    return false;
  }

  public function setAccessLevelForUser(User $user, $accessLevel = null)
  {
    foreach ($this->_users as $projectUser) {
      if ($user->getId() == $projectUser->getUserId()) {
        $projectUser->setAccess($accessLevel);
        return true;
      }
    }
    return false;
  }

  public function getNotificationsFromUser(User $user)
  {
    foreach ($this->_users as $projectUser) {
      if ($user->getId() == $projectUser->getUserId()) {
        return $projectUser->getNotifications();
      }
    }
    return false;
  }

  public function loadUsers()
  {
    $ret = Project_User::getList($this);
    $this->setUsers($ret);
  }

  /**
   * Logs an event to the project log.
   *
   * @param string $msg
   */
  public function log($msg, $username = '', $type = 0)
  {
    $projectLog = new Project_Log($this);
    $projectLog->setType($type);
    $projectLog->setMessage($msg);
    $projectLog->setUsername($username);
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
    foreach ($this->_users as $projectUser) {
      if ($user->getId() == $projectUser->getUserId()) {
        $hasAccessLevel = ($projectUser->getAccess() >= $accessLevel);
        break;
      }
    }
    SystemEvent::raise(SystemEvent::DEBUG, "User " . ($hasAccessLevel?'has':"doesn't have") . " access. [USER={$user->getUsername()}] [ACCESS={$accessLevel}] [PROJECTACCESSLEVEL={$projectUser->getAccess()}]", __METHOD__);
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

  /**
   * Fetches a user's project list for which he has at least READ access
   * @param User $user The user to fetch the project list from.
   * @param int $access An access permission value.
   * @param array $options Options to this method.
   */
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

  static public function &getNextToBuild(Array $options = array())
  {
    isset($options['pageStart'])?:$options['pageStart']=0;
    isset($options['pageLength'])?:$options['pageLength']=CINTIENT_NEXT_TO_BUILD_PAGE_LENGTH;

    $ret = array();
    $sql = 'SELECT * FROM project p'
         . " WHERE datecheckedforchanges < DATETIME('now', -(scmcheckchangestimeout) || ' minutes', 'localtime')"
         . ' ORDER BY datecheckedforchanges ASC LIMIT ?, ?';
    if ($rs = Database::query($sql, array($options['pageStart'], $options['pageLength']))) {
      while ($rs->nextRow()) {
        $ret[] = self::_getObject($rs);
      }
    }
    return $ret;
  }

  /**
   *
   * @param unknown_type $rs
   */
  static private function &_getObject(Resultset $rs, Array $options = array())
  {
    isset($options['loadUsers'])?:$options['loadUsers']=true;
    $ret = new Project();
    $ret->setAvatar($rs->getAvatar());
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
    $specialTasks = @unserialize($rs->getSpecialTasks());
    if ($specialTasks === false) {
      $specialTasks = array();
    }
    $ret->setSpecialTasks($specialTasks);
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
      $integrationBuilder = new Build_BuilderElement_Project();
    }
    $ret->setIntegrationBuilder($integrationBuilder);

    $unsafeSerializedDeploymentBuilder = str_replace(CINTIENT_NULL_BYTE_TOKEN, "\0", $rs->getDeploymentBuilder());
    if (($deploymentBuilder = unserialize($unsafeSerializedDeploymentBuilder)) === false) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't unserialize deployment builder for this project [PID={$ret->getId()}]");
      $deploymentBuilder = new Build_BuilderElement_Project();
    }
    $ret->setDeploymentBuilder($deploymentBuilder);

    if ($options['loadUsers']) {
      $ret->loadUsers();
    }
    $ret->resetSignature();
    return $ret;
  }
}