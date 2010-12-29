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
 * One special case with this class is that all data persistence handling is
 * done automatically, i.e., there's no need to call save() from an outside
 * scope. Usage is simple: new objects created from scratch, don't forget to
 * call init(); objects created from the database, no need to do anything.
 */
class Project
{
  private $_buildLabel;            // The build label to be used in the packages' and builds' nomenclature (together with the counter)
  private $_dateBuild;             // Last *success* build date
  private $_dateCheckedForChanges; // Last status check on the project (not necessarily originating a build)
  private $_dateCreation;
  private $_dateModification;      // Last settings modification date
  private $_description;
  private $_id;
  private $_releasemajor;          // The current release major number
  private $_releaseminor;          // The current release minor number
  private $_releasecounter;        // the *last* number assigned to a successful created release package. Should be incremental
  private $_scmConnectorType;      // * Always * loaded from the available modules on core/ScmConnector
  private $_scmPassword;
  private $_scmRemoteRepository;
  private $_scmUsername;
  private $_signature;             // Internal flag to control whether a save to database is required
  private $_status;
  private $_title;
  private $_users;                 // An array of users and corresponding permissions, taken from projectuser table
  private $_visits;                // Counter of accesses, for hotness
  private $_workDir;               // The working dir of the project (sources, generated reports, etc)
  //
  // Builders
  //
  private $_integrationBuilder;    // The builder used for continuous integration builds and package creation (serialized)
  private $_deploymentBuilder;     // The builder available inside a package, for deployment (serialized)
  //
  // Options
  //
  private $_optionPackageOnSuccess;  // Generate a release package on every successful build?

  const STATUS_UNINITIALIZED = 0;
  const STATUS_ERROR = 1;
  const STATUS_OK = 2;
  const STATUS_BUILDING = 3;
  const STATUS_MODIFIED = 4;

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
    $this->_buildLabel = '';
    $this->_description = '';
    $this->_scmConnectorType = SCM_DEFAULT_CONNECTOR;
    $this->_scmRemoteRepository = '';
    $this->_scmUsername = '';
    $this->_scmPassword = '';
    $this->_signature = null;
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
    $this->_save();
  }
  
  public function build($force = false)
  {
    $params = array();
    $params['type'] = $this->getScmConnectorType();
    $params['remote'] = $this->getScmRemoteRepository();
    $params['local'] = $this->getScmLocalWorkingCopy();
    $params['username'] = $this->getScmUsername();
    $params['password'] = $this->getScmPassword();

    if ($this->getStatus() == self::STATUS_BUILDING) {
      SystemEvent::raise(SystemEvent::INFO, "Project is currently building, or is queued for building. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }
    
    if ($this->getStatus() != self::STATUS_MODIFIED) {
      //
      // Checkout required?
      //
      if ($this->getStatus() == self::STATUS_UNINITIALIZED || !file_exists($this->getScmLocalWorkingCopy())) {
        if (!ScmConnector::checkout($params)) {
          SystemEvent::raise(SystemEvent::INFO, "Couldn't checkout sources. [PROJECTID={$this->getId()}]", __METHOD__);
          $this->setStatus(self::STATUS_ERROR);
          return false;
        }
        $this->setStatus(self::STATUS_MODIFIED);
      } else {
        if (!ScmConnector::isModified($params)) {
          SystemEvent::raise(SystemEvent::INFO, "No modifications detected. [PROJECTID={$this->getId()}]", __METHOD__);
          if (!$force) {
            $this->setStatus(self::STATUS_OK);
            return false;
          }
        }
        $this->setStatus(self::STATUS_MODIFIED);
        if (!ScmConnector::update($params)) {
          SystemEvent::raise(SystemEvent::INFO, "Couldn't update local sources. [PROJECTID={$this->getId()}]", __METHOD__);
          if (!$force) {
            $this->setStatus(self::STATUS_ERROR);
            return false;
          }
        }
      }
    }
    
    // 3. trigger unit tests and all the rules specified in the rules engine
    if (!($this->_integrationBuilder instanceof BuilderElement_Project)) {
      SystemEvent::raise(SystemEvent::DEBUG, "No valid integration builder specified. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }
    if ($this->_integrationBuilder->isEmpty()) {
      SystemEvent::raise(SystemEvent::DEBUG, "Empty integration builder. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }
    
    //
    // Ok. After this, we're dealing with a full-fledged build here.
    //
    if (empty($this->_buildLabel)) {
      SystemEvent::raise(SystemEvent::ERROR, "Empty build label, could not complete project build. [PROJECTID={$this->getId()}]", __METHOD__);
      $this->setStatus(self::STATUS_MODIFIED);
      return false;
    }
    $this->setStatus(self::STATUS_OK); // Even if the build fails, it's not really an error of the project
    $php = $this->_integrationBuilder->toString('php');
    $buildOk = BuilderConnector_Php::execute($php);
    $build = new ProjectBuild($this->getId());
    $build->setOutput(implode("\n", $GLOBALS['result']['stacktrace']));
    if (!$buildOk) {
      $build->setStatus(ProjectBuild::STATUS_FAIL);
      SystemEvent::raise(SystemEvent::INFO, "Integration build failed. [PROJECTID={$this->getId()}]", __METHOD__);
      return false;
    }
    //
    // Success. Increment the build counter to prepare it for next version
    //
    $this->setReleaseCounter(((int)$this->getReleaseCounter()+1));
    $build->setLabel($this->getBuildLabel() . '-' . $this->getReleaseMajor() . '.' . $this->getReleaseMinor() . '.' . $this->getReleaseCounter());
    
    // TODO: 5. generate release package?
    if ($this->getOptionPackageOnSuccess()) {
      $build->setStatus(ProjectBuild::STATUS_OK_WITH_PACKAGE);
    } else {
      $build->setStatus(ProjectBuild::STATUS_OK_WITHOUT_PACKAGE);
    }
    
    // TODO: 6. tag the sources on the built revision

    SystemEvent::raise(SystemEvent::INFO, "Integration build successful. [PROJECTID={$this->getId()}]", __METHOD__);
    return true;
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
    $this->updateSignature(); // No more saves for this project
    return true;
  }
  
  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    return md5(serialize($arr));
  }
  
  public function getDateCreation()
  {
    if (empty($this->_dateCreation)) {
      $this->_dateCreation = date('Y-m-d H:i:s');
    }
    return $this->_dateCreation;
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
    // TODO: Temporary: create dummy integration builder, while there is
    //       no interface to do this.
    //
    //
    //TODO: test code - every new project gets a pre-loaded integration builder
    //
    $echo = new BuilderElement_Task_Echo();
    $echo->setMessage('PHP lint checking...');
    $target = new BuilderElement_Target();
    $target->setName('build');
    $lint = new BuilderElement_Task_PhpLint();
    $fileset = new BuilderElement_Type_Fileset();
    $fileset->setDir('/Users/pfonseca/Dev/cintient/');
    $fileset->addInclude('**/*.php');
    $lint->setFilesets(array($fileset));
    $phpunit = new BuilderElement_Task_PhpUnit();
    $fileset2 = new BuilderElement_Type_Fileset();
    $fileset2->setDir('/Users/pfonseca/Dev/cintient/src/tests/');
    $fileset2->addInclude('*Test.php');
    $phpunit->setFilesets(array($fileset2));
    $phpunit->setLogJunitXmlFile($this->getReportsWorkingDir() . 'log-junit.xml');
    $phpunit->setCodeCoverageXmlFile($this->getReportsWorkingDir() . 'codecoverage.xml');
    $phpunit->setCodeCoverageHtmlFile($this->getReportsWorkingDir() . 'codecoverage.html');
    $echo2 = new BuilderElement_Task_Echo();
    $echo2->setMessage('Done!');
    $target->setTasks(array($echo, $lint, $phpunit, $echo2));
    $this->_integrationBuilder->addTarget($target);
    $this->_integrationBuilder->setDefaultTarget($target->getName());
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
    if (!ProjectBuild::install($this->getId())) {
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
    $this->setStatus(self::STATUS_OK);
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
  deploymentbuilder TEXT,
  description TEXT DEFAULT '',
  integrationbuilder TEXT,
  releasemajor MEDIUMINT UNSIGNED DEFAULT 0,
  releaseminor MEDIUMINT UNSIGNED DEFAULT 0,
  releasecounter INT UNSIGNED DEFAULT 0,
  scmconnectortype VARCHAR(20) DEFAULT '',
  scmpassword VARCHAR(255) DEFAULT '',
  scmremoterepository VARCHAR(255) DEFAULT '',
  scmusername VARCHAR(255) DEFAULT '',
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
  
  private function _save($force=false)
  {
    if ($this->_getCurrentSignature() == $this->_signature && !$force) {
      SystemEvent::raise(SystemEvent::DEBUG, "Project save called, but no saving is required.", __METHOD__);
      return false;
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
         . ' buildlabel,scmpassword,scmusername,workdir,'
         . ' scmremoterepository,scmconnectortype)'
         . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
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
      $this->getScmPassword(),
      $this->getScmUsername(),
      $this->getWorkDir(),
      $this->getScmRemoteRepository(),
      $this->getScmConnectorType(),
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
    $this->updateSignature();
    return true;
  }
  
  public function addToUsers(array $pair)
  {
    $this->_users = array_merge($this->_users, array($pair));
  }
  
  public function loadUsers()
  {
    $sql = "SELECT * FROM projectuser WHERE projectid=?";
    if ($rs = Database::query($sql, array($this->getId()))) {
      $ret = array();
      while ($rs->nextRow()) {
        $ret[] = array($rs->getUserId(), $rs->getAccess());
      }
    }
    $this->setUsers($ret);
  }
  
  public function updateSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }
  
  static public function getById($user, $id, $access = Access::READ, array $options = array())
  {
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
  
  /**
   * 
   * @param unknown_type $rs
   */
  static private function _getObject($rs, $options = array())
  {
    isset($options['loadUsers'])?:$options['loadUsers']=true;
    $ret = new Project();
    $ret->setScmConnectorType($rs->getScmConnectorType());
    $ret->setScmRemoteRepository($rs->getScmRemoteRepository());
    $ret->setScmUsername($rs->getScmUsername());
    $ret->setScmPassword($rs->getScmPassword());
    $ret->setWorkDir($rs->getWorkDir());
    $ret->setBuildLabel($rs->getBuildLabel());
    $ret->setDateCreation($rs->getDateCreation());
    $ret->setDescription($rs->getDescription());
    $ret->setId($rs->getId());
    $ret->setReleaseMajor($rs->getReleaseMajor());
    $ret->setReleaseMinor($rs->getReleaseMinor());
    $ret->setReleaseCounter($rs->getReleaseCounter());
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
    $ret->updateSignature();
    return $ret;
  }
}