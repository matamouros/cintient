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
    if (($pos = strpos($_SESSION['user']->getAvatar(), 'local:')) === 0) {
      @unlink(CINTIENT_AVATARS_DIR . substr($_SESSION['user']->getAvatar(), 6));
    }
    do {
      $filename = uniqid() . '.jpg';
      if (!file_exists(CINTIENT_AVATARS_DIR . $filename)) {
        break;
      }
    } while (true);
    $_SESSION['user']->setAvatarLocal($filename);

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
    echo json_encode(array('success' => true, 'url' => URLManager::getForAsset($filename, array('avatar' => 1))));
    exit;
  }
  
  static public function project_build()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);

    if (!isset($_SESSION['project']) || !($_SESSION['project'] instanceof Project)) {
      $msg = 'Invalid request';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
      ));
      exit;
    }
    if (!$_SESSION['project']->userHasAccessLevel($_SESSION['user'], Access::BUILD)) {
      $msg = 'Not authorized';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      echo json_encode(array(
        'success' => false,
        'error' => $msg,
        'projectStatus' => $_SESSION['project']->getStatus(),
      ));
      exit;
    }

    ProjectLog::write("A building was triggered.");
    if (!$_SESSION['project']->build(true)) {
      ProjectLog::write("Building failed.");
      echo json_encode(array(
        'success' => true,
        'projectStatus' => $_SESSION['project']->getStatus(),
      ));
    } else {
      ProjectLog::write("Building successful.");
      echo json_encode(array(
        'success' => true,
        'projectStatus' => $_SESSION['project']->getStatus(),
      ));
    }
    exit;
  }
}