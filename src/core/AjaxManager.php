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
 * All AJAX requests are session based, i.e., no GET parameters should be passed
 * for the current user and project.
 *
 * Mapping rules:
 *
 * URL => method name => template filename:
 * . /foo         => foo()        => foo.tpl         (default section)
 * . /foo-bar     => fooBar()     => foo-bar.tpl     (default section)
 * . /foo/bar     => foo_bar()    => foo/bar.tpl     (foo section)
 * . /foo/foo-bar => foo_fooBar() => foo/foo-bar.tpl (foo section)
 *
 * @package View
 */
class AjaxManager
{
  /* +----------------------------------------------------------------+ *\
  |* | DEFAULT                                                        | *|
  \* +----------------------------------------------------------------+ */

  static public function authentication()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (isset($GLOBALS['user']) && $GLOBALS['user'] instanceof User) {
      $msg = "User '{$GLOBALS['user']->getUsername()}' authenticated, taking you to your dashboard. Stand by...";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => true,
          'error' => $msg,
        )
      );
      exit;
    } else {
      $msg = "User '{$_POST['authenticationForm']['username']['value']}' failed to authenticate. Please try again.";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
  }

  static public function avatarUpload()
  {
    if (!isset($_GET['qqfile'])) {
      $msg = "No avatar file was uploaded.";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    //
    // Check server-side upload limits
    //
    $postSize = Utility::phpIniSizeToBytes(ini_get('post_max_size'));
    $uploadSize = Utility::phpIniSizeToBytes(ini_get('upload_max_filesize'));
    if ($postSize < CINTIENT_AVATAR_MAX_SIZE || $uploadSize < CINTIENT_AVATAR_MAX_SIZE) {
      $size = max(1, CINTIENT_AVATAR_MAX_SIZE / 1024 / 1024) . 'M';
      $msg = "Avatar max file size too big. Increase post_max_size and upload_max_filesize to $size";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    //
    // Checking content length
    //
    if (!isset($_SERVER["CONTENT_LENGTH"])) {
      $msg = "No support for fetching CONTENT_LENGTH from server.";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    $size = (int)$_SERVER["CONTENT_LENGTH"];
    if ($size == 0) {
      $msg = "Avatar file is empty.";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    if ($size > CINTIENT_AVATAR_MAX_SIZE) {
      $msg = 'Avatar file is too large.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    //
    // File handling.
    //
    $input = fopen("php://input", "r");
    $temp = tempnam(CINTIENT_WORK_DIR, 'avatartmp');
    $output = fopen($temp, "w");
    $realSize = stream_copy_to_stream($input, $output);
    fclose($input);
    fclose($output);

    if ($realSize != $size) {
      $msg = "Problems with the uploaded avatar file size. [CONTENT_LENGTH={$size}] [FILE_SIZE={$realSize}]";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    if (!file_exists($temp)) {
      $msg = "Uploaded avatar file couldn't be saved to temporary dir. [TEMP_FILE={$temp}]";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    $fileStats = getimagesize($temp);
    if ($fileStats[2] != IMAGETYPE_JPEG && $fileStats[2] != IMAGETYPE_PNG) {
      unlink($temp);
      $msg = "Avatar file with unsupported format. [FORMAT={$fileStats['mime']}]";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    $image = null;
    if ($fileStats[2] == IMAGETYPE_JPEG) {
      $image = @imagecreatefromjpeg($temp);
    }
    if ($fileStats[2] == IMAGETYPE_PNG) {
      $image = @imagecreatefrompng($temp);
    }
    if (!$image) {
      unlink($temp);
      $msg = "Problems creating image.";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }

    // We can be dealing with both project and user avatars here
    $subject = $GLOBALS['project'];
    if (!isset($_GET['p'])) {
      $subject = $GLOBALS['user'];
    }

    //
    // Save. If the there is a previous local avatar, extract it and
    // remove it from the filesystem.
    //
    if (($pos = strpos($subject->getAvatar(), 'local:')) === 0) {
      @unlink(CINTIENT_AVATARS_DIR . substr($subject->getAvatar(), 6));
    }
    do {
      $filename = uniqid() . '.jpg';
      if (!file_exists(CINTIENT_AVATARS_DIR . $filename)) {
        break;
      }
    } while (true);
    $subject->setAvatarLocal($filename);

    $dstPath = CINTIENT_AVATARS_DIR . $filename;
    $dstImg = imagecreatetruecolor(CINTIENT_AVATAR_WIDTH, CINTIENT_AVATAR_HEIGHT);
    if (!imagecopyresampled($dstImg, $image, 0, 0, 0, 0, CINTIENT_AVATAR_WIDTH, CINTIENT_AVATAR_HEIGHT, $fileStats[0], $fileStats[1])) {
      unlink($temp);
      $msg = "Problems creating image.";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    imagedestroy($image);
    if (!imagejpeg($dstImg, $dstPath, CINTIENT_AVATAR_IMAGE_QUALITY)) {
      unlink($temp);
      $msg = "Problems creating image.";
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    imagedestroy($dstImg);
    unlink($temp);
    //
    // Don't htmlspecialchars encode the following success JSON, since
    // the final URL will get encoded and on the client side via JS we
    // won't be able to easily turn that into an URL to refresh avatar
    //
    echo json_encode(array('success' => true, 'url' => UrlManager::getForAsset($filename, array('avatar' => 1))));
    exit;
  }

  static public function dashboard_project()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($_REQUEST['pid'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!(($project = Project::getById($GLOBALS['user'], $_REQUEST['pid'], Access::READ)) instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    // The following is probably redundant because above the project is
    // already fetched with the Access constriction.
    if (!$project->userHasAccessLevel($GLOBALS['user'], Access::READ) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    //
    // We need to process a Smarty file... Fuck tha police!
    //
    require_once 'lib/Smarty-3.0rc4/Smarty.class.php';
    $smarty = new Smarty();
    $smarty->setAllowPhpTag(true);
    $smarty->setCacheLifetime(0);
    $smarty->setDebugging(SMARTY_DEBUG);
    $smarty->setForceCompile(SMARTY_FORCE_COMPILE);
    $smarty->setCompileCheck(SMARTY_COMPILE_CHECK);
    $smarty->setTemplateDir(SMARTY_TEMPLATE_DIR);
    $smarty->setCompileDir(SMARTY_COMPILE_DIR);
    $smarty->error_reporting = error_reporting();
    Framework_SmartyPlugin::init($smarty);
    $smarty->assign('project_buildStats', Project_Build::getStats($project, $GLOBALS['user']));
    $smarty->assign('project_log', Project_Log::getList($project, $GLOBALS['user']));
    $smarty->assign('project_build', Project_Build::getLatest($project, $GLOBALS['user']));
    $smarty->assign('project', $project);
    $smarty->display('includes/dashboardProject.inc.tpl');
    exit;
  }

  static public function project_accessLevel()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not authorized',
        )
      );
      exit;
    }

    if (!isset($_POST['change']) ||
        !($params = explode('_', $_POST['change'])) ||
        count($params) != 2 ||
        Access::getDescription($params[1]) === false)
    {
      SystemEvent::raise(SystemEvent::INFO, "Invalid request parameters. " . (isset($_POST['change'])?"[PARAMS={$_POST['change']}]":""), __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Invalid request',
        )
      );
      exit;
    }

    $user = User::getByUsername($params[0]);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$params[0]}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not found',
        )
      );
      exit;
    }

    if ($GLOBALS['project']->setAccessLevelForUser($user, $params[1])) {
      echo json_encode(
        array(
        	'success' => true,
        	'error' => 'Saved!',
        )
      );
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Could not update access level for user. [USERNAME={$user->getUsername()}] [ACCESSLEVEL={$params[1]}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Could not update access level for user',
        )
      );
    }
    exit;
  }

  static public function project_addUser()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not authorized',
        )
      );
      exit;
    }

    $user = User::getByUsername($_GET['username']);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$_GET['username']}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not found',
        )
      );
      exit;
    }

    $GLOBALS['project']->addToUsers($user);
    $accessLevels = Access::getList();

    $html = <<<EOT
            <li id="{$user->getUsername()}">
              <div class="avatar40"><img src="{$user->getAvatarUrl()}" width="40" height="40"></div>
              <div class="username"><h3>{$user->getUsername()}</h3></div>
              <div class="actionItems">
EOT;
    if (!$GLOBALS['project']->userHasAccessLevel($user, Access::OWNER)) {
      $removeLink = UrlManager::getForAjaxProjectRemoveUser($user->getUsername());
      $html .= <<<EOT
                <div class="remove"><a class="{$user->getUsername()} btn danger" href="{$removeLink}">Remove</a></div>
                <div class="access"><a class="{$user->getUsername()} btn" href="#">Access</a></div>
                <div class="popover-wrapper">
                  <div id="accessLevelPaneLevels_{$user->getUsername()}" class="accessLevelPopover popover above">
                    <div class="arrow"></div>
                    <div class="inner">
                      <h3 class="title">Access level</h3>
                      <div class="content">
                        <ul class="inputs-list">
EOT;
      foreach ($accessLevels as $accessLevel => $accessName) {
        if ($accessLevel !== 0) {
          $checked = '';
          if ($GLOBALS['project']->getAccessLevelFromUser($user) == $accessLevel) {
            $checked = ' checked';
          }
          $accessName = ucfirst($accessName);
          $html .= <<<EOT
                          <li>
                            <input type="radio" value="{$user->getUsername()}_{$accessLevel}" name="accessLevel_{$user->getUsername()}" id="{$accessLevel}"{$checked} />
                            <span>{$accessName}</span>
                          </li>
EOT;
        }
      }
      $html .= "
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>";
    } else {
      $html .= '
                <div class="noChanges">Owner (no changes allowed)</div>';
		}
    $html .= "
              </div>
            </li>";

    echo json_encode(
      array(
        'success' => true,
        'html' => $html,
      )
    );

    exit;
  }

  static public function project_build()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::BUILD) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
          'projectStatus' => $GLOBALS['project']->getStatus(),
        )
      );
      exit;
    }
    $GLOBALS['project']->log("A building was triggered.");
    if (!$GLOBALS['project']->build(true)) {
      $GLOBALS['project']->log("Build failed!");
      echo json_encode(
        array(
          'success' => true,
          'projectStatus' => $GLOBALS['project']->getStatus(),
        )
      );
    } else {
      $GLOBALS['project']->log("Build successful.");
      echo json_encode(
        array(
          'success' => true,
          'projectStatus' => $GLOBALS['project']->getStatus(),
        )
      );
    }
    exit;
  }

	/**
   *
   */
  static public function project_integrationBuilderAddElement()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        empty($_REQUEST['task']) || empty($_REQUEST['parent'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    $class = 'Build_BuilderElement' . $_REQUEST['parent'] . '_' . $_REQUEST['task'];
    if (!class_exists($class)) {
      $msg = 'Unexisting builder element';
      SystemEvent::raise(SystemEvent::INFO, $msg . " [ELEMENT={$class}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $element = $class::create();
    $GLOBALS['project']->addToIntegrationBuilder($element);
    $GLOBALS['project']->log("Integration builder changed, element added.");
    SystemEvent::raise(SystemEvent::DEBUG, "Builder element added.", __METHOD__);

    echo $element->toHtml();
    exit;
  }

	/**
   *
   */
  static public function project_integrationBuilderDeleteElement()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        empty($_REQUEST['internalId'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    $element = $GLOBALS['project']->getIntegrationBuilder()->getElement($_REQUEST['internalId']);
    if (!$element->isDeletable()) {
      $msg = 'Builder element not deletable';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $GLOBALS['project']->removeFromIntegrationBuilder($element);

    SystemEvent::raise(SystemEvent::DEBUG, "Builder element removed.", __METHOD__);
    echo json_encode(
      array(
      	'success' => true,
      )
    );
    exit;
  }

	/**
   * Saves one builder element at a time on project edit.
   */
  static public function project_integrationBuilderSaveElement()
  {
    /*

    matamouros 2011.05.18:

    In order to simpflify a great deal of things, I've opted for the following:
    . filesets in HTML are abstracted as seamlessly part of the respective
      tasks where they are used. In truth, filesets should be completely
      independent editable forms, and tasks should then reference them
      (perhaps on a select dropdown).
    . The above means that, for now, only one fileset is allowed for
      each task that uses it.

    Alas, this simplification accounts for slightly bloatier code in
    this method, namely because we now have to special case the processing
    of seamless embedded filesets and make sure that backstage they are
    still properly updating the corresponding Build_BuilderElement_Fileset object.

    */
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    SystemEvent::raise(SystemEvent::DEBUG, print_r($_REQUEST, true), __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        empty($_REQUEST['internalId'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $o = $GLOBALS['project']->getIntegrationBuilder()->getElement($_REQUEST['internalId']['value']);
    if (!($o instanceof Build_BuilderElement)) {
      $msg = 'Unknown task specified';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$o->isEditable()) {
      $msg = 'Builder element not editable';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    foreach ($_REQUEST as $attributeName => $attributeValue) {
      $method = 'set' . ucfirst($attributeName);
      if (!isset($attributeValue['value'])) { // Unselected radio buttons cause the value attribute to not be sent
        $attributeValue['value'] = null;
      }
      $value = filter_var($attributeValue['value'], FILTER_SANITIZE_STRING);
      if ($attributeValue['type'] == 'checkbox') {
        $value = ($attributeValue['value'] ? true : false);
      }
      //
      // Specific handling for filesets
      //
      if (($attributeName == 'include' || $attributeName == 'exclude' ||
           $attributeName == 'defaultExcludes' || $attributeName == 'dir' ||
           $attributeName == 'type' ) &&
          ($o instanceof Build_BuilderElement_Task_Php_PhpUnit ||
           $o instanceof Build_BuilderElement_Task_Php_PhpLint ||
           $o instanceof Build_BuilderElement_Task_Filesystem_Chmod ||
           $o instanceof Build_BuilderElement_Task_Filesystem_Delete
          )
      ) {
        $filesets = $o->getFilesets(); // Only one is expected, for now
        if ($attributeName == 'include' || $attributeName == 'exclude') {
          $value = array($value);
        } elseif ($attributeName == 'type' &&
                  $value != Build_BuilderElement_Type_Fileset::BOTH &&
                  $value != Build_BuilderElement_Type_Fileset::FILE &&
                  $value != Build_BuilderElement_Type_Fileset::DIR  )
        {
          $value = Build_BuilderElement_Type_Fileset::getDefaultType();
        }
        $filesets[0]->$method($value);
      } else {
        $o->$method($value);
      }
    }

    $GLOBALS['project']->log("Integration builder changed.");

    SystemEvent::raise(SystemEvent::DEBUG, "Builder element properly edited.", __METHOD__);
    echo json_encode(
      array(
      	'success' => true,
      )
    );
    exit;
  }

  static public function project_integrationBuilderSortElements()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        empty($_REQUEST['sortedElements'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }
    //
    // It works like this: in here we get an array of ordered IDs. We assume
    // those IDs are all at the same level. We get the first ID and go get
    // that element's parent. We sort all the elements corresponding to
    // the IDs and then reassign them to the parent element.
    //
    // For now, and for simplification purposes, we assume that we are
    // always only sorting tasks and that all parents are always a target.
    //
    $parent = $GLOBALS['project']->getIntegrationBuilder()->getParent($_REQUEST['sortedElements'][0]);
    $parent->setTasks($parent->sortElements($_REQUEST['sortedElements']));

    $GLOBALS['project']->log("Integration builder changed, reordered tasks.");

    SystemEvent::raise(SystemEvent::DEBUG, "Project tasks reordered.", __METHOD__);
    echo json_encode(
      array(
      	'success' => true,
      )
    );
    exit;
  }

  static public function project_delete()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    $postVars = array();
    foreach ($_POST['deleteForm'] as $name => $value) {
      if (isset($value['value'])) {
        $postVars[$name] = $value['value'];
      }
    }

    // Make sure the user knows what he is deleting
    if ($GLOBALS['project']->getId() != $postVars['pid']) {
      $msg = 'Trying to delete a different project from the current active one is not allowed.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    // User access level
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::OWNER) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
        'success' => false,
        'error' => $msg,
        )
      );
      exit;
    }

    $ret = $GLOBALS['project']->delete();
    if (!$ret) {
      $msg = 'Project could not be deleted. Please check the logs.';
      SystemEvent::raise(SystemEvent::ERROR, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    } else {
      session_start();
      $GLOBALS['project'] = $_SESSION['projectId'] = null;
      unset($GLOBALS['project']);
      unset($_SESSION['projectId']);
      session_write_close();
      $msg = 'Project deleted, taking you back to your dashboard. Stand by...';
      SystemEvent::raise(SystemEvent::INFO, "Project deleted. [PID={$postVars['pid']}] [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(
        array(
          'success' => true,
          'error' => $msg,
        )
      );
      exit;
    }
  }

  static public function project_editGeneral()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $postVars = $_POST['generalForm'];

    if (empty($postVars['title']['value']) || empty($postVars['buildLabel']['value'])) {
      // TODO: visual clue for required attributes, in the interface
      $msg = 'Required attributes were empty.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $project = $GLOBALS['project'];
    $project->setTitle($postVars['title']['value']);
    $project->setBuildLabel($postVars['buildLabel']['value']);
    $project->setDescription($postVars['description']['value']);
    $GLOBALS['project'] = $project;
    $msg = "Project general settings edited by user {$GLOBALS['user']->getUsername()}.";
    $GLOBALS['project']->log($msg);
    SystemEvent::raise(SystemEvent::DEBUG, $msg, __METHOD__);
    echo json_encode(
      array(
  			'success' => true,
      )
    );
    exit;
  }

  static public function project_editScm()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $postVars = $_POST['scmForm'];

    if (empty($postVars['scmConnectorType']['value']) || empty($postVars['scmRemoteRepository']['value'])) {
      // TODO: visual clue for required attributes, in the interface
      $msg = 'Required attributes were empty.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $project = $GLOBALS['project'];
    $project->setScmConnectorType($postVars['scmConnectorType']['value']);
    $project->setScmRemoteRepository($postVars['scmRemoteRepository']['value']);
    $project->setScmUsername($postVars['scmUsername']['value']);
    $project->setScmPassword($postVars['scmPassword']['value']);
    if ($postVars['scmConnectorType']['value'] != $project->getScmConnectorType()) {
      $project->resetScmConnector();
    }
    $GLOBALS['project'] = $project;
    $msg = "Project SCM settings edited by user {$GLOBALS['user']->getUsername()}.";
    $GLOBALS['project']->log($msg);
    SystemEvent::raise(SystemEvent::DEBUG, $msg, __METHOD__);
    echo json_encode(
      array(
  			'success' => true,
      )
    );
    exit;
  }

  static public function project_new()
  {
    $postVars = array();
    foreach ($_POST['newProjectContainer'] as $name => $value) {
      if (isset($value['value'])) {
        $postVars[$name] = $value['value'];
      }
    }
    //
    // Check for mandatory attributes
    //
    if (!isset($postVars['title']) ||
         empty($postVars['title']) ||
        !isset($postVars['buildLabel']) ||
         empty($postVars['buildLabel']) ||
        !isset($postVars['scmConnectorType']) ||
         empty($postVars['scmConnectorType']) ||
        !isset($postVars['scmRemoteRepository']) ||
         empty($postVars['scmRemoteRepository'])
    ) {
      //
      // TODO: Error notification!!!
      //
      SystemEvent::raise(SystemEvent::DEBUG, "Project creation failed, required attributes were empty.", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Missing required attributes. Make sure all attributes not marked as optional are filled.',
        )
      );
      exit;
    } else {
      $GLOBALS['project'] = null;
      $project = new Project();
      $project->setTitle($postVars['title']);
      $project->setBuildLabel($postVars['buildLabel']);
      $project->setScmConnectorType($postVars['scmConnectorType']);
      $project->setScmRemoteRepository($postVars['scmRemoteRepository']);
      $project->setScmUsername($postVars['scmUsername']);
      $project->setScmPassword($postVars['scmPassword']);
      $project->addToUsers(
        $GLOBALS['user'],
        Access::OWNER
      );
      if (!$project->init()) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not initialize project. Try again later.", __METHOD__);
        echo json_encode(
          array(
            'success' => false,
            'error' => 'The project was created but could not be initialized. Please check the logs and/or try the first build manually.',
          )
        );
        exit;
      }
      $GLOBALS['project'] = $project;
      $GLOBALS['project']->log("Project created.");
      echo json_encode(
        array(
          'success' => true
        )
      );
      exit;
    }
  }

  static public function project_notificationsSave()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    // Pull up the user's notifications
    $projectUser = Project_User::getByUser($GLOBALS['project'], $GLOBALS['user']);

    $notificationSettings = $projectUser->getNotifications();
    $newNotificationSettings = array();

    foreach ($_POST['notificationsForm'] as $key => $value) {
      $keyParts = explode('_', $key);
      $event = $keyParts[0];
      $handler = 'Notification_' . $keyParts[1];
      if (!isset($newNotificationSettings[$handler])) {
        $newNotificationSettings[$handler] = array();
      }
      $newNotificationSettings[$handler][$event] = (bool)($value['value']);
    }
    $projectUser->setNotifications(new NotificationSettings($GLOBALS['project'], $GLOBALS['user'], $newNotificationSettings));

    $GLOBALS['project']->log("Notification settings changed for user {$GLOBALS['user']->getUsername()}.");
    SystemEvent::raise(SystemEvent::DEBUG, "Project notification settings changed for user {$GLOBALS['user']->getUsername()}.", __METHOD__);
    echo json_encode(
      array(
  			'success' => true,
      )
    );
    exit;
  }

  static public function project_removeUser()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        !isset($_POST['username'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not authorized',
        )
      );
      exit;
    }

    $user = User::getByUsername($_POST['username']);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$_POST['username']}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not found',
        )
      );
      exit;
    }

    //
    // Don't remove owners
    //
    if ($GLOBALS['project']->userHasAccessLevel($user, Access::OWNER)) {
      SystemEvent::raise(SystemEvent::INFO, "Only owners can remove themselves. [USER={$user->getUsername()}]", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Not authorized',
        )
      );
      exit;
    }

    echo json_encode(
      array(
        'success' => $GLOBALS['project']->removeFromUsers($user),
        'username' => $user->getUsername(),
      )
    );
    exit;
  }

  static public function registration()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    //
    // Check for validity
    //
    if (empty($_POST['registrationForm']['name']['value']) ||
        empty($_POST['registrationForm']['email']['value']) ||
        empty($_POST['registrationForm']['username']['value']) ||
        empty($_POST['registrationForm']['password']['value']) ||
        empty($_POST['registrationForm']['password2']['value'])
    ) {
      SystemEvent::raise(SystemEvent::DEBUG, "User registration failed, required attributes were empty.", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => 'Please fill in all input fields.',
        )
      );
      exit;

    } else if ($_POST['registrationForm']['password']['value'] != $_POST['registrationForm']['password2']['value']) {
      SystemEvent::raise(SystemEvent::DEBUG, "Passwords don't match.", __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => "The passwords don't match.",
        )
      );
      exit;

    } else {
      $user = User::getByUsername($_POST['registrationForm']['username']['value']);
      if ($user instanceof User) {
        SystemEvent::raise(SystemEvent::DEBUG, "Username already taken.", __METHOD__);
        echo json_encode(
          array(
            'success' => false,
            'error' => 'The username you provided is already taken.',
          )
        );
        exit;
      }
      $user = null;
      unset($user);
    }
    //
    // Everything ok, let's register the new user
    //
    $user = new User();
    $user->setEmail($_POST['registrationForm']['email']['value']);
    //$user->setNotificationEmails($_POST['registrationForm']['email']['value']);
    $user->setName($_POST['registrationForm']['name']['value']);
    $user->setUsername($_POST['registrationForm']['username']['value']);
    $user->setCos(UserCos::USER);
    $user->init();
    $user->setPassword($_POST['registrationForm']['password']['value']);
    //
    // Log the user in
    //
    /*
    Auth::authenticate();
    */
    SystemEvent::raise(SystemEvent::INFO, "New user created. [USERNAME={$user->getUsername()}]", __METHOD__);
    echo json_encode(
      array (
        'success' => true,
        'error' => 'Registration was successful, taking you to the login prompt. Stand by...',
      )
    );
    exit;
  }

  /**
   * Searches for a list of users, given a specified term.
   */
  static public function search_user()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called. [USERTERM={$_GET['userTerm']}]", __METHOD__);

    //
    // This should be a costly operation for the user waiting.
    //
    sleep(1);

    //
    // Also, don't blindly check one letter only
    //
    if (strlen($_GET['userTerm']) < 2) {
      SystemEvent::raise(SystemEvent::DEBUG, "Not triggering query for less than 2 chars. [USERTERM={$_GET['userTerm']}]", __METHOD__);
      echo json_encode(
        array(
        	'success' => false,
        )
      );
      exit;
    }

    $usersToJson = array();
    $users = User::getListByIncompleteTerm($_GET['userTerm']);
    for ($i = 0; $i < count($users); $i++) {
      $usersToJson[$i] = array();
      $usersToJson[$i]['username'] = $users[$i]->getUsername();
      $usersToJson[$i]['avatar'] = $users[$i]->getAvatarUrl();
    }

    echo json_encode(
      array(
        'success' => true,
        'result' => $usersToJson,
      )
    );
    exit;
  }

  static public function settings_notificationsSave()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['user']) || !($GLOBALS['user'] instanceof User)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(
        array(
          'success' => false,
          'error' => $msg,
        )
      );
      exit;
    }

    $notifications = array();
    foreach ($_REQUEST as $handler => $payload) {
      $notificationClass = /*'Notification_' . */$handler;
      if (!class_exists($notificationClass)) {
        SystemEvent::raise(SystemEvent::INFO, "Invalid notification handler specified for save. [METHOD={$handler}]", __METHOD__);
        continue;
      }
      $notification = new $notificationClass();
      foreach ($payload as $attribute => $data) {
        $setter = 'set' . ucfirst($attribute);
        if (!is_callable(array($notification, $setter))) {
          // method_exists() does not invoke __call() to check the method exists
          SystemEvent::raise(SystemEvent::INFO, "Invalid notification handler attribute specified for save. [METHOD={$handler}] [ATTRIBUTE={$attribute}]", __METHOD__);
          continue;
        }
        $notification->$setter($data['value']);
      }
      $notifications[$handler] = $notification;
    }
    $GLOBALS['user']->setNotifications($notifications);

    SystemEvent::raise(SystemEvent::DEBUG, "Notification settings changed for user {$GLOBALS['user']->getUsername()}.", __METHOD__);
    echo json_encode(
      array(
  			'success' => true,
      )
    );
    exit;
  }
}