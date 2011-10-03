<?php
if (false) {
?>
Cintient requires a PHP environment in order to run. Please install PHP and try again.
<!--
<?php
}
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
 *
 *  This is Cintient's installation script. The only way to make it past
 *  installation is to delete this, which should be done automatically at
 *  the very end of this script, if install is successful.
 *
 *  @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 *
 */


error_reporting(-1);
ini_set('display_errors', 'off');

// Control var, so that we can guess if we're exiting because of an
// error or because of normal script execution.
$exited = false;

//
// Make sure no previous .htaccess file is present here and refuse to
// run if we can't delete it. If present, our DOCUMENT_ROOT and
// SCRIPT_FILENAME might become poluted, breaking Cintient afterwards.
//
if (is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess')) {
  if (!@unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess')) {
    die("An .htaccess file is present in Cintient's directory and couldn't be removed. Please remove it manually and try again.");
  }
}

//
// Following are default values for the installation script
//
// All cases considered (all with and without trailing slash):
// . /a/b/c/index.php
// . /a/b/c/index.php?a=1&b=1
// . /a/b/c/
// . /a
// . /
$uriPathInfo = pathinfo(strtok($_SERVER['REQUEST_URI'], '?'));
$reqUri = $uriPathInfo['dirname'];
if ($uriPathInfo['basename'] != 'index.php') {
  if ($reqUri != '/') {
    $reqUri .= '/';
  }
  $reqUri .= $uriPathInfo['basename'];
}
$defaults = array();
$defaults['appWorkDir'] = realpath(dirname(__FILE__) . '/..') . '/.cintient/';
$defaults['baseUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . ($reqUri != '/' ? $reqUri : ''); # No trailing slash
$defaults['configurationDir'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src/config/';
$defaults['configurationSampleFile'] = $defaults['configurationDir'] . 'cintient.conf.sample';
$defaults['configurationNewFile'] = $defaults['configurationDir'] . 'cintient.conf.php';
$defaults['htaccessFile'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess';

//
// Utility functions
//
// Returns a modified configuration file, given a directive and value
//
function directiveValueUpdate($str, $directive, $directiveValue)
{
  return preg_replace('/(define\s*\(\s*(?:\'|")' . $directive . '(?:\'|")\s*,\s*(?:\'|"))(.*)((?:\'|")\s*\);)/', '$1' . $directiveValue . '$3', $str);
}

//
// The response generator. Will terminate execution promptly.
//
function sendResponse($ok, $msg)
{
  global $exited;
  $exited = true;
  echo htmlspecialchars(
    json_encode(
      array(
        'ok'  => $ok,
        'msg' => $msg,
      )
    ),
    ENT_NOQUOTES
  );
  exit;
}

/**
 * A shutdown function to be registered in all AJAX calls. This ensures
 * that a default error message is always sent to the client, in case of
 * unexpected error.
 *
 * Basically the mechanism is: if a response wasn't sent back by
 * sendResponse(), then takeover and send a generic error, asking the
 * user to check the PHP error log. We know that all AJAX calls should
 * terminate on sendResponse(), so any unexpected errors will fallback
 * to this callback.
 */
function cleanup()
{
  global $exited;
  if (!$exited) {
    // Assume error
    sendResponse(false, 'Sorry, but there was an unknown error. Please check your PHP error log.');
  }
}

//
// Following are function definitions for all checkable items
//
function phpInstallationVersion()
{
  $msg[0] = "Version 5.3.3 or higher is required.";
  $ok = false;
  if (function_exists('phpversion') && function_exists('version_compare')) {
    $msg[0] .= " Version " . phpversion() . " detected.";
    $msg[1] = "Detected version " . phpversion() . ".";
    $ok = version_compare(phpversion(), '5.3.3', '>=');
  }
  return array($ok, $msg[(int)$ok]);
}

function phpWithSqlite()
{
  $msg[0] = "PHP with sqlite3 version 2.5 or higher required.";
  $ok = false;
  if (extension_loaded('sqlite3') && function_exists('sqlite_libversion')) {
    $msg[0] .= " Version " . sqlite_libversion() . " detected.";
    $msg[1] = "Detected version " . sqlite_libversion() . ".";
    $ok = extension_loaded('sqlite3') && sqlite_libversion() > '2.5';
  }
  return array($ok, $msg[(int)$ok]);
}

function apacheModRewrite()
{
  $msg[0] = "Apache mod_rewrite is required.";
  $msg[1] = "Detected.";
  $ok = false;
  if (function_exists('apache_get_modules')) {
    $ok = in_array("mod_rewrite", apache_get_modules());
  }
  return array($ok, $msg[(int)$ok]);
}

function baseUrl($url)
{
  $msg[0] = "Check that you have specified a valid URL.";
  $msg[1] = "Ready.";
  $ok = true; // TODO!
  return array($ok, $msg[(int)$ok]);
}

function appWorkDir($dir)
{
  $msg[0] = "Check dir exists and enable write permissions.";
  $msg[1] = "Ready.";
  if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
    $dir .= DIRECTORY_SEPARATOR;
  }
  $testDir = $dir . '.install';
  $ok = (@mkdir($testDir, 0777, true) != false);
  @rmdir($testDir);
  return array($ok, $msg[(int)$ok]);
}

function htaccessFile($dir)
{
  $msg[0] = "Enable write permissions in this dir.";
  $msg[1] = "Ready.";
  $fd = @fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess', 'a+');
  if ($fd === false) {
    $ok = false;
  } else {
    $ok = true;
  }
  @fclose($fd);
  return array($ok, $msg[(int)$ok]);
}

function configurationDir($dir)
{
  global $defaults;
  $msg[0] = "Enable write permissions in this dir.";
  $msg[1] = "Ready.";
  // Must be able to read the sample file provided and either create the
  // unexisting new one, or backup an existing one and change the original
  $testFilename = $defaults['configurationNewFile'] . uniqid();
  $fd1 = @fopen($defaults['configurationSampleFile'], 'r');
  $fd2 = @fopen($testFilename, 'a');
  if ($fd1 === false || $fd2 === false) {
    $ok = false;
  } else {
    $ok = true;
  }
  @fclose($fd1);
  @fclose($fd2);
  @unlink($testFilename);
  return array($ok, $msg[(int)$ok]);
}

//
// AJAX check requests
//
if (!empty($_GET['c'])) {
  register_shutdown_function('cleanup'); // If an error occurs, this will show the user some info
  $ok = false;
  $msg = 'Invalid request';
  // TODO: Sanatize input
  $c = $_GET['c'];
  $v = (empty($_GET['v'])?null:$_GET['v']);
  if (function_exists($c)) {
    list($ok, $msg) = $c($v);
  }
  sendResponse($ok, $msg);
//
// Final form submission
//
} elseif (!empty($_GET['s'])) {
  register_shutdown_function('cleanup'); // If an error occurs, this will show the user some info
  $ok = false;
  $msg = "Invalid request";

  sleep(3); # Avoids a race condition at the end of the installation process while updating UI elements
  define('CINTIENT_INSTALLER_DEFAULT_DIR_MASK', 0700);

  //
  // Extract all sent inputs into key/value pairs
  //
  $get = array();
  foreach ($_GET as $key => $value) {
    // TODO: filter everything
    if ($key != '_' && $key != 's') {
      if ($key == 'appWorkDir' && substr($value, -1) != DIRECTORY_SEPARATOR) {
        $value .= DIRECTORY_SEPARATOR;
      } elseif ($key == 'baseUrl' && substr($value, -1) == '/') {
        $value = substr($value, 0, -1);
      }
      $get[$key] = $value;
    }
  }

  //
  // Write the .htaccess file
  //
  $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess';
  $fd = @fopen($file, 'w');
  if ($fd !== false) {
    fwrite($fd, "RewriteEngine on\n");
    fwrite($fd, "RewriteBase {$reqUri}/\n"); # Insert a trailing slash here, it's needed!
    fwrite($fd, "RewriteRule (fonts)/(.*) www/\$1/\$2 [L]\n");
    fwrite($fd, "RewriteRule (imgs)/(.*) www/\$1/\$2 [L]\n");
    fwrite($fd, "RewriteRule (js)/(.*) www/\$1/\$2 [L]\n");
    fwrite($fd, "RewriteRule (css)/(.*) www/\$1/\$2 [L]\n");
    fwrite($fd, "RewriteRule ajax src/handlers/ajaxHandler.php [L]\n");
    fwrite($fd, "RewriteRule .* src/handlers/webHandler.php [L]\n");
    fclose($fd);
  } else {
    $ok = false;
    $msg = "Couldn't create the .htaccess file in " . dirname(__FILE__) . DIRECTORY_SEPARATOR;
    sendResponse($ok, $msg);
  }

  //
  // The configuration file
  //
  if (($fdSample = fopen($defaults['configurationSampleFile'], 'r')) === false) {
    $ok = false;
    $msg = "Couldn't read the sample configuration file {$defaults['configurationSampleFile']}. Check dir permissions.";
    sendResponse($ok, $msg);
  }
  $originalConfFile = fread($fdSample, filesize($defaults['configurationSampleFile']));
  $modifiedConfFile = $originalConfFile;
  // Replacements:
  $modifiedConfFile = directiveValueUpdate($modifiedConfFile, 'CINTIENT_WORK_DIR', $get['appWorkDir']);
  $modifiedConfFile = directiveValueUpdate($modifiedConfFile, 'CINTIENT_BASE_URL', $get['baseUrl']);
  fclose($fdSample);
  // If the configuration file already exists, back it up
  if (file_exists($defaults['configurationNewFile'])) {
    if (!@copy($defaults['configurationNewFile'], $defaults['configurationNewFile'] . '_' . date("Ymd") . '_' . time())) {
      $ok = false;
      $msg = "Couldn't backup an existing configuration file {$defaults['configurationNewFile']}. Check dir permissions.";
      sendResponse($ok, $msg);
    }
    @unlink($defaults['configurationNewFile']);
  }
  file_put_contents($defaults['configurationNewFile'], $modifiedConfFile);

  //
  // From here on Cintient itself will handle the rest of the installation
  //
  require $defaults['configurationNewFile'];
  error_reporting(-1);
  ini_set('display_errors', 'off');
  //
  // Create necessary dirs
  //
  if (!file_exists(CINTIENT_WORK_DIR) && !@mkdir(CINTIENT_WORK_DIR, DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create working dir. Check your permissions.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_PROJECTS_DIR) && !@mkdir(CINTIENT_PROJECTS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create projects dir. Check your permissions.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_ASSETS_DIR) && !@mkdir(CINTIENT_ASSETS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create assets dir. Check your permissions.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_AVATARS_DIR) && !@mkdir(CINTIENT_AVATARS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create avatars dir. Check your permissions.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  //
  // Setup all objects
  //
  if (!User::install()) {
    $ok = false;
    $msg = "Could not setup User object.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!Project::install()) {
    $ok = false;
    $msg = "Could not setup Project object.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!SystemSettings::install()) {
    $ok = false;
    $msg = "Could not setup SystemSettings object.";
    SystemEvent::raise(128, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  //
  // Root user account
  //
  $user = new User();
  $user->setEmail($get['email']);
  $user->setNotificationEmails($get['email'] . ',');
  $user->setName('Administrative Account');
  $user->setUsername('root');
  $user->setCos(/*UserCos::ROOT*/ 2);
  $user->init();
  $user->setPassword($get['password']);

  //
  // Last step: remove the installation file
  //
  if (!@unlink(__FILE__)) {
    $ok = false;
    $msg = "Couldn't remove the installation 'index.php' file. You need "
         . "to remove this manually before refreshing this page, or else"
         . " Cintient won't be able to start";
    sendResponse($ok, $msg);
  }

  $ok = true;
  $msg = "Use 'root' and the password you provided to login. Please refresh this page when you are ready.";
  SystemEvent::raise(1024, "Installation successful.", "Installer");
  sendResponse($ok, $msg);
}

//
// Ok ready to start installation!
//
$greetings = array(
  'Greetings human.',
  "I'm sorry, Dave, I'm afraid I can't do that.",
  "They'll fix you. They fix everything.",
  'Looking for me?',
  'Stay out of trouble.',
  "This will all end in tears.",
  "Danger, Will Robinson!",
  "Thank you for a very enjoyable game.",
  "Shall we play a game?",
  "Wouldn't you prefer a nice game of chess?",
  "Greetings, Professor Falken.",
);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title>Cintient Installation</title>
  <link rel="stylesheet" href="www/css/font_anonymouspro.css" />
  <link rel="stylesheet" href="www/css/font_orbitron.css" />
  <link rel="stylesheet" href="www/css/font_syncopate.css" />
  <link rel="stylesheet" href="www/css/global.css" />
  <link rel="stylesheet" href="www/css/installer.css" />
  <link rel="icon" href="www/favicon.ico">
  <!--[if lt IE 9]>
  <script src="www/js/lib/html5.js"></script>
  <![endif]-->
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="www/js/lib/jquery-1.6.js"></script>
  <script type="text/javascript" src="www/js/installer.js"></script>
</head>
<body id="installer">
  <div id="splashHeader" class="container">
    <header>
      <hgroup>
        <h1 style="display: none;">Cintient</h1>
        <img style="display: none;" src="www/imgs/redhalo.jpg" width="195" height="130">
      </hgroup>
    </header>
    <div class="greetings" style="display: none;"><?php echo $greetings[rand(0, count($greetings)-1)]; ?></div>
  </div>
  <div id="header" class="containerTopLevel">
    <div id="userHeader" class="container">
      <header>
        <hgroup>
          <h1 id="logo" style="display: none;">Cintient <img src="www/imgs/redhalo_45.jpg" height="25"></h1>
        </hgroup>
      </header>
    </div>
  </div>

  <div id="menu" class="containerTopLevel">
    <div id="mainMenu" style="display: none;">
      <ul>
        <li id="historyBack">
          <span class="step-1">&#8226;</span><span class="step-2 ghosted">&#8226;</span><span class="step-3 ghosted">&#8226;</span>
        </li>
        <li id="sectionName"></li>
      </ul>
    </div>
  </div>
  <div class="containerTopLevel">

    <div id="step-1" class="installerStep noDisplay container">
      <div class="stepTitle" style="display: none;">Minimum requirements</div>
      <div>
        <ul class="item">
<?php
list ($ok, $msg) = phpInstallationVersion();
?>
          <li id="phpInstallationVersion">
            <div class="label">PHP 5.3.x</div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = phpWithSqlite();
?>
          <li id="phpWithSqlite">
            <div class="label">PHP with SQLite3 2.5.x</div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = apacheModRewrite();
?>
          <li id="apacheModRewrite">
            <div class="label">Apache mod_rewrite</div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
        </ul>
      </div>
    </div>

    <div id="step-2" class="installerStep noDisplay container">
      <div class="stepTitle" style="display: none;">Basic setup</div>
      <div>
        <ul class="item">
<?php
list ($ok, $msg) = baseUrl($defaults['baseUrl']);
?>
          <li class="inputCheckOnChange" id="baseUrl">
            <div class="label">Base URL where Cintient will run from</div>
            <div class="fineprintLabel">(Cintient tried to guess it. If you are not sure, just go with its suggestion)</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" type="text" name="baseUrl" value="<?php echo $defaults['baseUrl']; ?>" style="width: 450px;" /></div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = appWorkDir($defaults['appWorkDir']);
?>
          <li class="inputCheckOnChange" id="appWorkDir">
            <div class="label">Work files dir</div>
            <div class="fineprintLabel">(place for working files and databases, independent from the installation dir)</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" style="width: 450px;" type="text" name="appWorkDir" value="<?php echo $defaults['appWorkDir']; ?>" /></div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = htaccessFile($defaults['htaccessFile']);
?>
          <li class="inputCheckOnChange" id="htaccessFile">
            <div class="label">.htaccess</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" disabled="disabled" style="width: 450px;" type="text" name="htaccessFile" value="<?php echo $defaults['htaccessFile']; ?>" /></div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = configurationDir($defaults['configurationDir']);
?>
          <li class="inputCheckOnChange" id="configurationDir">
            <div class="label">Configuration dir</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" disabled="disabled" style="width: 450px;" type="text" name="configurationDir" value="<?php echo $defaults['configurationDir']; ?>" /></div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
        </ul>
      </div>
    </div>

    <div id="step-3" class="installerStep noDisplay container">
      <div class="stepTitle" style="display: none;">Administration account</div>
      <div>
        <ul class="item">
          <li class="inputCheckOnChange" id="email">
            <div class="label">Email</div>
            <div class="fineprintLabel">(for administration notifications)</div>
            <div class="textfieldContainer" style="width: 306px;"><input class="textfield" style="width: 300px;" type="email" name="email" value="" /></div>
            <div class="result <?php $ok = false; $msg = 'Email field is empty/invalid'; echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
          <li class="inputCheckOnChange" id="password">
            <div class="label">Password</div>
            <div class="textfieldContainer" style="width: 206px;"><input class="textfield" style="width: 200px;" type="password" name="password" value="" /></div>
            <div class="fineprintLabel">(and again here, to make sure you remember what you typed above)</div>
            <div class="textfieldContainer" style="width: 206px;"><input class="textfield" style="width: 200px;" type="password" name="passwordr" value="" /></div>
            <div class="result <?php $ok = false; $msg = "Passwords don't match"; echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
        </ul>
      </div>
    </div>

    <div id="actionButtons" class="container"></div>

    <div id="done" class="noDisplay container"></div>

  </div>
<script type="text/javascript">
// <![CDATA[
//inputLocalCheckOnChange validation function
function inputCheckOnChangeEmail()
{
  var input = $("#step-3 .item #email input").val();
  var msg = ['Email field is empty/invalid', 'Ready.'];
  var ok = (input.length > 1);
  return {ok: ok, msg: msg[Number(ok)]}; // TODO: check email better
}
//inputLocalCheckOnPassword validation function
function inputCheckOnChangePassword()
{
  var input1 = $("#step-3 .item #password input[name=password]").val();
  var input2 = $("#step-3 .item #password input[name=passwordr]").val();
  var msg = ["Passwords don't match", 'Ready.'];
  var ok = (input1.length > 0 && input1 == input2);
  return {ok: ok, msg: msg[Number(ok)]};
}
$(document).ready(function() {
  new Installer({step:0});
});
// ]]>
</script>
</body>
</html>