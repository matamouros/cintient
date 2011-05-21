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
 */
class AjaxManager
{  
  /* +----------------------------------------------------------------+ *\
  |* | DEFAULT                                                        | *|
  \* +----------------------------------------------------------------+ */

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
    if ($postSize < CINTIENT_AVATAR_MAX_SIZE || $uploadSize < CINTIENT_AVATAR_MAX_SIZE){
      $size = max(1, CINTIENT_AVATAR_MAX_SIZE / 1024 / 1024) . 'M';
      $msg = "Avatar max file size too big. Increase post_max_size and upload_max_filesize to $size";
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo htmlspecialchars(json_encode(array('error' => $msg)), ENT_NOQUOTES);
      exit;
    }
    
    //
    // Checking content length
    //
    if (!isset($_SERVER["CONTENT_LENGTH"])){
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
    
    if ($realSize != $size){
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
    
    //
    // Save. If the user has a previous local avatar, extract it and
    // remove it from the filesystem.
    //
    if (($pos = strpos($GLOBALS['user']->getAvatar(), 'local:')) === 0) {
      @unlink(CINTIENT_AVATARS_DIR . substr($GLOBALS['user']->getAvatar(), 6));
    }
    do {
      $filename = uniqid() . '.jpg';
      if (!file_exists(CINTIENT_AVATARS_DIR . $filename)) {
        break;
      }
    } while (true);
    $GLOBALS['user']->setAvatarLocal($filename);

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
  
  static public function project_accessLevel()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not authorized',
      ));
      exit;
    }
    
    if (!isset($_GET['change']) ||
        !($params = explode('_', $_GET['change'])) ||
        count($params) != 2 ||
        Access::getDescription($params[1]) === false) {
      SystemEvent::raise(SystemEvent::INFO, "Invalid request parameters. " . (isset($_GET['change'])?"[PARAMS={$_GET['change']}]":""), __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Invalid request',
      ));
      exit;
    }
    
    $user = User::getByUsername($params[0]);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$params[0]}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not found',
      ));
      exit;
    }
    
    if ($GLOBALS['project']->setAccessLevelForUser($user, $params[1])) {
      echo json_encode(array(
        'success' => true,
      ));
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Could not update access level for user. [USERNAME={$user->getUsername()}] [ACCESSLEVEL={$params[1]}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Could not update access level for user',
      ));
    }

    exit;
  }
  
  static public function project_addUser()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not authorized',
      ));
      exit;
    }
    
    $user = User::getByUsername($_GET['username']);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$_GET['username']}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not found',
      ));
      exit;
    }
    
    $GLOBALS['project']->addToUsers(array($user->getId(), Access::DEFAULT_USER_ACCESS_LEVEL_TO_PROJECT));
    $accessLevels = Access::getList();
    $html = <<<EOT
            <li id="{$user->getUsername()}" style="display: none;">
              <div class="user">
                <div class="avatar"><img src="{$user->getAvatarUrl()}" width="40" height="40"></div>
                <div class="username">{$user->getUsername()}</div>
EOT;
    if (!$GLOBALS['project']->userHasAccessLevel($user, Access::OWNER)) {
      $removeLink = UrlManager::getForAjaxProjectRemoveUser($user->getUsername());
      $html .= <<<EOT
                <div class="remove"><a class="{$user->getUsername()}" href="{$removeLink}">remove</a></div>
                <div class="accessLevelPane">
                  <div class="accessLevelPaneTitle"><a href="#" class="{$user->getUsername()}">access level</a></div>
                  <div id="accessLevelPaneLevels_{$user->getUsername()}" class="accessLevelPaneLevels">
                    <ul>
EOT;
      foreach ($accessLevels as $accessLevel => $accessName) {
        if ($accessLevel !== 0) {
          $checked = '';
          if ($GLOBALS['project']->getAccessLevelFromUser($user) == $accessLevel) {
            $checked = ' checked';
          }
          $accessName = ucfirst($accessName);
          $html .= <<<EOT
                      <li><input class="accessLevelPaneLevelsCheckbox" type="radio" value="{$user->getUsername()}_{$accessLevel}" name="accessLevel" id="{$accessLevel}"{$checked} /><label for="{$accessLevel}" class="labelCheckbox">{$accessName}<div class="fineprintLabel" style="display: none;">{Access::getDescription($accessLevel)}</div></label></li>
EOT;
        }
      }
      $html .= "
                    </ul>
                  </div>
                </div>";
    } else {
      $html .= '
                <div class="remove">Owner <span class="fineprintLabel">(no changes allowed)</span></div>';
    }
    $html .= "
              </div>
            </li>";
    
    echo json_encode(array(
      'success' => true,
      'html' => $html,
    ));

    exit;
  }
  
  static public function project_build()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::BUILD) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
        'projectStatus' => $GLOBALS['project']->getStatus(),
      ));
      exit;
    }

    $GLOBALS['project']->log("A building was triggered.");
    if (!$GLOBALS['project']->build(true)) {
      $GLOBALS['project']->log("Building failed.");
      echo json_encode(array(
        'success' => true,
        'projectStatus' => $GLOBALS['project']->getStatus(),
      ));
    } else {
      $GLOBALS['project']->log("Building successful.");
      echo json_encode(array(
        'success' => true,
        'projectStatus' => $GLOBALS['project']->getStatus(),
      ));
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
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    $class = 'BuilderElement' . $_REQUEST['parent'] . '_' . $_REQUEST['task'];
    if (!class_exists($class)) {
      $msg = 'Unexisting builder element';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    $element = new $class();
    /*
    
    matamouros 2011.05.21:
    
    In order to simpflify a great deal of things, I've opted for the following:
    . filesets in HTML are abstracted as seamlessly part of the respective
      tasks where they are used. In truth, filesets should be completely
      independent editable forms, and tasks should then reference them
      (perhaps on a select dropdown).
    . The above means that, for now, only one fileset is allowed for
      each task that uses it.
    
    This means that right up next, we have pretty ugly code coming up,
    setting up filesets preemptively for specific types of tasks. For now...
    
    */
    if ($_REQUEST['task'] == 'PhpUnit' ||
        $_REQUEST['task'] == 'PhpLint' ||
        $_REQUEST['task'] == 'Delete' ||
    ) {
      $fileset = new BuilderElement_Type_Fileset();
      $element->setFilesets(array($fileset));
    }
    
    $targets = $GLOBALS['project']->getIntegrationBuilder()->getTargets();
    $target = $targets[0];
    $target->addTask($element);
    
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
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    $newBuilder = $GLOBALS['project']->getIntegrationBuilder()->deleteElement($_REQUEST['internalId']);
    $GLOBALS['project']->setIntegrationBuilder($newBuilder);
    $GLOBALS['project']->log("Integration builder changed, element removed.");
    
    SystemEvent::raise(SystemEvent::DEBUG, "Builder element removed.", __METHOD__);
    echo json_encode(array(
      'success' => true,
    ));
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
    still properly updating the corresponding BuilderElement_Fileset object.
    
    */
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (empty($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        empty($_REQUEST['internalId'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    $o = $GLOBALS['project']->getIntegrationBuilder()->getElement($_REQUEST['internalId']['value']);
    if (!($o instanceof BuilderElement)) {
      $msg = 'Unknown task specified';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }

    foreach ($_REQUEST as $attributeName => $attributeValue) {
      $method = 'set' . ucfirst($attributeName);
      $value = $attributeValue['value'];
      if ($attributeValue['type'] == 'checkbox') {
        $value = ($attributeValue['value'] ? true : false);
      }
      if (($attributeName == 'include' || $attributeName == 'exclude' ||
           $attributeName == 'defaultExcludes' || $attributeName == 'dir') &&
          ($o instanceof BuilderElement_Task_PhpUnit || $o instanceof BuilderElement_Task_PhpLint)
      ) {
        $filesets = $o->getFilesets(); // Only one is expected, for now
        if ($attributeName == 'include' || $attributeName == 'exclude') {
          $value = array($value);
        }
        $filesets[0]->$method($value);
      } else {
        $o->$method($value);
      }
    }
    
    $GLOBALS['project']->log("Integration builder changed.");
    
    SystemEvent::raise(SystemEvent::DEBUG, "Builder element properly edited.", __METHOD__);
    echo json_encode(array(
      'success' => true,
    ));
    exit;
  }
  
  static public function project_removeUser()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project) ||
        !isset($_GET['username'])) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not authorized',
      ));
      exit;
    }
    
    $user = User::getByUsername($_GET['username']);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Username not found. [USERNAME={$_GET['username']}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not found',
      ));
      exit;
    }
    
    //
    // Don't remove owners
    //
    if ($GLOBALS['project']->userHasAccessLevel($user, Access::OWNER)) {
      SystemEvent::raise(SystemEvent::INFO, "Only owners can remove themselves. [USER={$user->getUsername()}]", __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => 'Not authorized',
      ));
      exit;
    }

    echo json_encode(array(
      'success' => $GLOBALS['project']->removeFromUsers($user),
      'username' => $user->getUsername(),
    ));
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
      echo json_encode(array(
        'success' => false,
      ));
      exit;
    }
    
    $usersToJson = array();
    $users = User::getListByIncompleteTerm($_GET['userTerm']);
    for ($i = 0; $i < count($users); $i++) {
      $usersToJson[$i] = array();
      $usersToJson[$i]['username'] = $users[$i]->getUsername();
      $usersToJson[$i]['avatar'] = $users[$i]->getAvatarUrl();
    }
    
    echo json_encode(array(
      'success' => true,
      'result' => $usersToJson,
    ));
    
    exit;
  }
}