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
 * Mapping rules:
 * 
 *   URI          => method name  => template filename:
 *   
 * . /foo         => foo()        => foo.tpl         (default section)
 * . /foo-bar     => fooBar()     => foo-bar.tpl     (default section)
 * . /foo/bar     => foo_bar()    => foo/bar.tpl     (foo section)
 * . /foo/foo-bar => foo_fooBar() => foo/foo-bar.tpl (foo section)
 * 
 * Smarty variable naming rules:
 * 
 * <method_name>_<variable_name>
 * Example: $s->assign('fooBar_foo'); => variable foo for method fooBar()
 */
class TemplateManager
{
  /* +----------------------------------------------------------------+ *\
  |* | TESTS                                                          | *|
  \* +----------------------------------------------------------------+ */

  static public function tests_init()
  {
    $user = new User();
    $user->setEmail('pedro.matamouros@gmail.com');
    $user->setNotificationEmails('pedro.matamouros@gmail.com,');
    $user->setName('Pedro Mata-Mouros');
    $user->setUsername('matamouros');
    $user->setCos(UserCos::ROOT);
    $user->init();
    $user->setPassword('pedro');
  }
  
  static public function tests_phpinfo()
  {
    echo phpinfo();
    exit;
  }
  
  static public function tests_showUser()
  {
    $user = User::getByUsername('matamouros');
    var_dump($user);
    exit;
  }
  
  static public function tests_svnCheckout()
  {
    $project = new Project(
      'svn',
      'svn://trac.intra.sapo.pt/fotos2/branches/2009.10.29_layout',
      '/tmp/project-ci2/',
      'pfonseca',
      'segula'
    );
    $project->init();
  }
  
  static public function tests_svnModified()
  {
    $project = new Project(
      'svn',
      'svn://trac.intra.sapo.pt/fotos2/branches/2009.10.29_layout',
      '/tmp/project-ci2/',
      'pfonseca',
      'segula'
    );
    $project->isModified();
  }
  
  static public function tests_tasks()
  {
    $exec = new BuilderElement_Task_Exec();
    $exec->setExecutable('ls -la');
    $exec->setArgs(array('extra/'));
    $exec->setDir('/tmp/apache');
    $exec->setOutputProperty('xpto');
    //echo $exec->toString('ant');
    
    $delete = new BuilderElement_Task_Delete();
    $delete->setIncludeEmptyDirs(true);
    $delete->setFailOnError(true);
    $fileset = new BuilderElement_Type_Fileset();
    $fileset->setDir('/tmp/apache');
    //$fileset->setDefaultExcludes(false);
    $fileset->setInclude(array('extra/**/*.conf'));
    $delete->setFilesets(array($fileset));
    //echo $delete->toString('ant');
    
    $echo = new BuilderElement_Task_Echo();
    $echo->setMessage('About to do an exec!');
    
    $echo2 = new BuilderElement_Task_Echo();
    $echo2->setMessage('About to do an exec2!');
    $echo2->setFile('/tmp/test.log');
    $echo2->setAppend(true);
    
    $mkdir = new BuilderElement_Task_Mkdir();
    //$mkdir->setDir('/tmp/tmp2/tmp3');
    $mkdir->setDir('/lixo');
    
    $lint = new BuilderElement_Task_PhpLint();
    $lint->setFilesets(array($fileset));
    
    $target = new BuilderElement_Target();
    $target->setName('tests');
    $target->setTasks(array($exec, $lint));
    //echo $target->toString('php');
    
    $target2 = new BuilderElement_Target();
    $target2->setName('tests2');
    //$target->setTasks(array($delete, $exec));
    $target2->setTasks(array($echo, $mkdir));
    //echo $target->toString('php');
    
    $project = new BuilderElement_Project();
    $project->addTarget($target);
    $project->setBaseDir('/tmp/');
    //$project->addTarget($target2);
    $project->setDefaultTarget($target->getName());
    $code = $project->toString('phing');
    
    echo $code;
    //var_dump(BuilderConnector_Php::execute($code));
    
    exit;
  }
  
  
  /* +----------------------------------------------------------------+ *\
  |* | DEFAULT                                                        | *|
  \* +----------------------------------------------------------------+ */
  
  static public function authentication()
  {
    if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) {
      header("Location: " . URLManager::getForDashboard());
      exit;
    }
  }
  
  static public function project()
  {
    //
    // New project form request
    //
    if (isset($_GET['new']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
      $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
      return true;
    }
    //
    // New project form submission
    //
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_POST['title'];
      $_POST['description'];
      $_POST['scmConnectorType'];
      $_POST['scmRemoteRepository'];
      $_POST['scmUsername'];
      $_POST['scmPassword'];
      
      //
      // TODO: On errors, put the textfields in a session var
      //
      
      $project = new Project();
      $project->setTitle($_POST['title']);
      $project->setBuildLabel($_POST['buildLabel']);
      $project->setDescription($_POST['description']);
      $project->setScmConnectorType($_POST['scmConnectorType']);
      $project->setScmRemoteRepository($_POST['scmRemoteRepository']);
      $project->setScmUsername($_POST['scmUsername']);
      $project->setScmPassword($_POST['scmPassword']);
      $project->addToUsers(array(
        $_SESSION['user']->getId(),
        Access::READ + Access::BUILD + Access::WRITE + Access::OWNER)
      );
      if (!$project->init()) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not initialize project. Try again later.", __METHOD__);
        //
        // TODO: Notification
        //
        header('Location: ' . UrlManager::getForProjectNew());
        exit;
      }
      $_SESSION['project'] = $project;
      ProjectLog::write("Project created.");
      
      //
      // TODO: Success notification to display after redirecting
      //
      
      //
      // Rig stuff to still use this request and use the view logic below
      //
      $_GET['pid'] = $project->getId();
      $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
    }
    
    //
    // Project view/edit
    //
    if (isset($_GET['pid']) && !empty($_GET['pid'])) {
      $_SESSION['project'] = Project::getById($_SESSION['user'], $_GET['pid']);
    }
    if (!isset($_SESSION['project']) || !($_SESSION['project'] instanceof Project)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems fetching requested project.", __METHOD__);
      //
      // TODO: Notification
      //
      //
      // TODO: this should really be a redirect to the previous page.
      //
      return false;
    }
    //
    // Building
    //
    if (isset($_GET['build'])) {
      ProjectLog::write("A building was triggered.");
      if (!$_SESSION['project']->build(true)) {
        ProjectLog::write("Building failed.");
      } else {
        ProjectLog::write("Building successful.");
      }
    //
    // Viewing details
    //
    } else {
      $GLOBALS['smarty']->assign('project_buildList', ProjectBuild::getListByProject($_SESSION['project'], $_SESSION['user']));
    }
  }
  
  /**
   * Shows a list of available projects to the current user, for selection. Any
   * project for which the user has at least read access level.
   */
  static public function dashboard()
  {
    $GLOBALS['smarty']->assign('dashboard_projectList', Project::getList($_SESSION['user'], Access::READ));
  }
  
  static public function install()
  {
    session_destroy();
    session_start();
    //
    // Create necessary dirs
    //
    if (!file_exists(WORK_DIR) && !mkdir(WORK_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create working dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!file_exists(PROJECTS_DIR) && !mkdir(PROJECTS_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create projects dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    //
    // Setup all objects
    //
    if (!User::install()) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not setup User object.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!Project::install()) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not setup Project object.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    //
    // Test user setup
    //
    self::tests_init();
    header('Location: ' . URLManager::getForDashboard());
    exit;
  }
  
  static public function notFound()
  {}
 
  static public function notAuthorized()
  {}
}