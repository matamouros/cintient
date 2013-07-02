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
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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

// Holds previously checked items
$report = array();

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
$reqUri = str_replace("\\", '/', $reqUri); // Windows is reported putting '\' in this string. This could break some location style paths though... Check it later.
$reqUri = str_replace('//', '/', $reqUri);
$defaults = array();
$defaults['appWorkDir'] = ''; //;realpath(dirname(__FILE__) . '/..') . '/.cintient/';
$defaults['baseUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . ($reqUri != '/' ? $reqUri : ''); # No trailing slash
$defaults['configurationDir'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src/config/';
$defaults['configurationSampleFile'] = $defaults['configurationDir'] . 'cintient.conf.sample';
$defaults['configurationNewFile'] = $defaults['configurationDir'] . 'cintient.conf.php';
$defaults['htaccessFile'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess';
define('CINTIENT_INSTALLER_VERSION', '1.5.0-dev');

//
// Utility functions
//
// Returns a modified configuration file, given a directive and value
//
function directiveValueUpdate($str, $directive, $directiveValue)
{
  return preg_replace('/(define\s*\(\s*(?:\'|")' . $directive . '(?:\'|")\s*,\s*(?:\'|")).*((?:\'|")\s*\);)/', '$1' . $directiveValue . '$2', $str);
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
        'ok'   => $ok,
        'desc' => $msg,
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
  $msg[0] = "PHP with SQLite3 required.";
  $ok = false;
  if (extension_loaded('sqlite3')) {
    // TODO: require >= 3.3.0 for CREATE TABLE IF NOT EXISTS support
    $version = SQLite3::version();
    $msg[0] .= " Please enable PHP with SQLite3.";
    $msg[1] = "Detected version " . $version['versionString'] . ".";
    $ok = extension_loaded('sqlite3');
  }
  return array($ok, $msg[(int)$ok]);
}

function modRewrite()
{
  global $report;
  $msg[0] = "Apache, Lighttpd or NGiNX is required.";
  $msg[1] = "Detected.";
  $ok = false;
  if (function_exists('apache_get_modules'))
  {
    $ok = in_array("mod_rewrite", apache_get_modules());
    $report['server'] = 'apache';
  }
  else if (strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false)
  {
    $ok = true;
    $msg[1] .= " Make sure you follow the instructions given to you a few screens down the road, in order to have Lighttpd working properly.";
    $report['server'] = 'lighttpd';
  }
  else if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false)
  {
    $ok = true;
    $msg[1] .= " Make sure you follow the instructions given to you a few screens down the road, in order to have NGiNX working properly.";
    $report['server'] = 'nginx';
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
  if (!empty($dir) && substr($dir, -1) != DIRECTORY_SEPARATOR) {
    $dir .= DIRECTORY_SEPARATOR;
  }
  //
  // Test for upgrade or clean install
  //
  if (empty($dir)) {
    $ok = false;
  } elseif (is_file($dir . 'cintient.sqlite')) {
    $msg[2] = $msg[1];
    $ok = 2;
  } else {
    $testDir = $dir . '.install';
    $ok = (@mkdir($testDir, 0777, true) != false);
    @rmdir($testDir);
  }
  return array($ok, $msg[(int)$ok]);
}

function htaccessFile($dir)
{
  global $report, $defaults;
  $msg[0] = "You cannot change the dir, just enable write permissions there.";
  $msg[1] = "Ready.";
  $file = '.htaccess';
  if ($report['server'] == 'lighttpd')
  {
    $file = 'cintient-lighttpd.conf';
  }
  else if ($report['server'] == 'nginx')
  {
    $file = 'cintient-nginx.conf';
  }
  $defaults['htaccessFile'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . $file;
  $fd = @fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . $file, 'a+');
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
  $msg[0] = "You cannot change the dir, just enable write permissions there.";
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

  // A date marker to be used in backup filenames and such
  $dateMarker = date("Ymd") . '_' . time();

  //
  // Extract all sent inputs into key/value pairs
  //
  $get = array();
  foreach ($_POST as $key => $value) {
    // TODO: filter everything
    if ($key != '_' && $key != 's') {
      if ($key == 'appWorkDir') {
        if (substr($value, -1) != DIRECTORY_SEPARATOR) {
          $value .= DIRECTORY_SEPARATOR;
        }
        //
        // Major issue in Windows platforms, where the '\' character escapes
        // the $2 in the directiveValueUpdate() call, thus never replacing it
        // with the proper "');" string and thus creating a syntax error.
        // That's why we're forced to make sure on the appWorkDir only
        // '/' are feeded to Cintient, and that this str_replace is *after*
        // the DIRECTORY_SEPARATOR add above (since in Windows it will end
        // the appWorkDir value with '\' again).
        //
        $value = str_replace('\\', '/', $value);
        $value = str_replace('//', '/', $value); // Just making sure the first str_replace doesn't add a / to an already existing /.
      } elseif ($key == 'baseUrl' && substr($value, -1) == '/') {
        $value = substr($value, 0, -1);
      }
      $get[$key] = $value;
    }
  }

  //
  // Write the .htaccess file
  //
  modRewrite();
  if ($report['server'] == 'apache'/*function_exists('apache_get_modules')*/) {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.htaccess';
    $fd = @fopen($file, 'w');
    if ($fd !== false) {
      fwrite($fd, "RewriteEngine on" . PHP_EOL);
      fwrite($fd, "RewriteBase {$reqUri}" . (substr($reqUri, -1)=='/'?'':'/') . PHP_EOL); # Insert a trailing slash here, it's needed!
      fwrite($fd, "RewriteRule (fonts|imgs|js|css)/(.*) www/\$1/\$2 [L]" . PHP_EOL);
      fwrite($fd, "RewriteRule ajax src/handlers/ajaxHandler.php [L]" . PHP_EOL);
      fwrite($fd, "RewriteRule favicon\\.ico www/imgs/favicon.ico [L]" . PHP_EOL);
      fwrite($fd, "RewriteRule .* src/handlers/webHandler.php [L]" . PHP_EOL);
      fclose($fd);
    } else {
      $ok = false;
      $msg = "Couldn't create the .htaccess file in " . dirname(__FILE__) . DIRECTORY_SEPARATOR;
      sendResponse($ok, $msg);
    }
  }
  else if ($report['server'] == 'lighttpd')
  {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cintient-lighttpd.conf';
    $fd = @fopen($file, 'w');
    if ($fd !== false) {
      $dirSep = DIRECTORY_SEPARATOR;
      fwrite($fd, "url.rewrite-once = (" . PHP_EOL);
      fwrite($fd, "\t\"{$reqUri}(?:.*)(fonts|imgs|js|css)/(.*)?\" => \"{$reqUri}{$dirSep}www{$dirSep}\$1{$dirSep}\$2\"," . PHP_EOL);
      fwrite($fd, "\t\"{$reqUri}(?:.*)ajax(?:[^?]*)(\?.*)?\" => \"{$reqUri}{$dirSep}src{$dirSep}handlers{$dirSep}ajaxHandler.php$1\"," . PHP_EOL);
      fwrite($fd, "\t\"{$reqUri}(?:.*)favicon\\.ico\" => \"{$reqUri}{$dirSep}www{$dirSep}imgs{$dirSep}favicon.ico\"," . PHP_EOL);
      fwrite($fd, "\t\"{$reqUri}(.*)\" => \"{$reqUri}{$dirSep}src{$dirSep}handlers{$dirSep}webHandler.php$1\"," . PHP_EOL);
      fwrite($fd, ")" . PHP_EOL);
      fclose($fd);
    } else {
      $ok = false;
      $msg = "Couldn't create the cintient-lighttpd.conf file in " . dirname(__FILE__) . DIRECTORY_SEPARATOR;
      sendResponse($ok, $msg);
    }
  }
  else if ($report['server'] == 'nginx')
  {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cintient-nginx.conf';
    $fd = @fopen($file, 'w');
    if ($fd !== false) {
      $dirSep = DIRECTORY_SEPARATOR;
      fwrite($fd, "# Add this config snippet inside your NGiNX server declaration." . PHP_EOL);
      fwrite($fd, "location {$reqUri} {" . PHP_EOL);
      $baseReqUri = $reqUri . ($reqUri[strlen($reqUri)-1] != '/'? '/': '');
      fwrite($fd, "\trewrite  (fonts|imgs|js|css)/(.*)   {$baseReqUri}www{$dirSep}\$1{$dirSep}\$2                     break;" . PHP_EOL);
      fwrite($fd, "\trewrite  favicon\\.ico               {$baseReqUri}www{$dirSep}imgs{$dirSep}favicon.ico          break;" . PHP_EOL);
      fwrite($fd, "\trewrite  ajax                       {$baseReqUri}src{$dirSep}handlers{$dirSep}ajaxHandler.php  last;"  . PHP_EOL);
      fwrite($fd, "\trewrite  .*                         {$baseReqUri}src{$dirSep}handlers{$dirSep}webHandler.php   last;"  . PHP_EOL);
      fwrite($fd, "}" . PHP_EOL);
      fclose($fd);
    } else {
      $ok = false;
      $msg = "Couldn't create the cintient-nginx.conf file in " . dirname(__FILE__) . DIRECTORY_SEPARATOR;
      sendResponse($ok, $msg);
    }
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
    if (!@copy($defaults['configurationNewFile'], $defaults['configurationNewFile'] . '_' . $dateMarker)) {
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
  SystemEvent::setSeverityLevel(SystemEvent::INFO);

  //
  // Check for upgrade or clean install
  //
  $upgrade = false;
  list ($ok, $_) = appWorkDir($get['appWorkDir']);
  if ($ok === 2) {
    $upgrade = true;
  }
  //
  // Authentication (user must be root)
  //
  if ($upgrade) {
    $_POST = array();
    $_POST['authenticationForm'] = array();
    $_POST['authenticationForm']['username'] = array();
    $_POST['authenticationForm']['username']['value'] = 'root';
    $_POST['authenticationForm']['password'] = array();
    $_POST['authenticationForm']['password']['value'] = $get['password'];
    if (!Auth_Local::authenticate()) {
      $ok = false;
      $msg = "The password you specified for the root account was invalid.";
      SystemEvent::raise(CINTIENT_LOG_SEVERITY_INFO, $msg, "Installer");
      sendResponse($ok, $msg);
    }
  }

  //
  // Create necessary dirs
  //
  if (!file_exists(CINTIENT_WORK_DIR) && !@mkdir(CINTIENT_WORK_DIR, DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create working dir. Check your permissions.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_PROJECTS_DIR) && !@mkdir(CINTIENT_PROJECTS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create projects dir. Check your permissions.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_ASSETS_DIR) && !@mkdir(CINTIENT_ASSETS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create assets dir. Check your permissions.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!file_exists(CINTIENT_AVATARS_DIR) && !@mkdir(CINTIENT_AVATARS_DIR, CINTIENT_INSTALLER_DEFAULT_DIR_MASK, true)) {
    $ok = false;
    $msg = "Could not create avatars dir. Check your permissions.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }

  //
  // Backup a previous version database, if found, and if possible
  //
  if ($upgrade && !@copy($get['appWorkDir'] . 'cintient.sqlite', $get['appWorkDir'] . "cintient_{$dateMarker}.sqlite")) {
    $msg = "Could not backup your previous version database. Continuing the upgrade, nevertheless.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
  }

  //
  // Force an exclusive database transaction
  //
  if (!Database::beginTransaction(Database::EXCLUSIVE_TRANSACTION)) {
    $ok = false;
    $msg = "Problems obtaining an exclusive lock on the database.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  //
  // Setup all objects
  //
  if (!User::install()) {
    $ok = false;
    $msg = "Could not setup User object.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!Project::install()) {
    $ok = false;
    $msg = "Could not setup Project object.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  if (!SystemSettings::install()) {
    $ok = false;
    $msg = "Could not setup SystemSettings object.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }
  //
  // Everything ok!!!
  //
  if (!Database::endTransaction()) {
    Database::rollbackTransaction();
    $ok = false;
    $msg = "Problems commiting all changes to the database.";
    SystemEvent::raise(CINTIENT_LOG_SEVERITY_ERROR, $msg, "Installer");
    sendResponse($ok, $msg);
  }

  $settings = SystemSettings::load();
  $settings->setSetting(SystemSettings::VERSION, CINTIENT_INSTALLER_VERSION);

  if (!$upgrade) {
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
  }

  // Just to make sure everything's neat and tidy, especially after
  // an upgrade.
  Database::execute('VACUUM');

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

  //
  // Set a special cookie "one-time" cookie so that right after the
  // installation we can show a message. This is just temporary until
  // system-user messages are implemented. This cookie will be imediately
  // erased by webHandler, after a GLOBAL flag is set. Right now a modal
  // is being shown in header.inc.tpl and this cookie is there removed.
  //
  setcookie('cintientInstalled', time());

  $ok = true;
  if ($upgrade) {
    $msg = "Cintient was successfully updated. Please refresh this page when you're ready.";
  } else {
    $msg = "Use 'root' and the password you provided to login. Please refresh this page when you're ready.";
  }
  SystemEvent::raise(CINTIENT_LOG_SEVERITY_INFO, "Installation successful.", "Installer");
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
  "I've seen things you people wouldn't believe.",
  "Do... or do not. There is no try.",
  "Live long and prosper.",
  "The beginning is a very delicate time.",
  "Tell me of your homeworld, Usul.",
);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title>Cintient Installation</title>
  <link rel="stylesheet" href="www/css/reset.css" />
  <link rel="stylesheet" href="www/css/font_anonymouspro.css" />
  <link rel="stylesheet" href="www/css/font_orbitron.css" />
  <link rel="stylesheet" href="www/css/font_syncopate.css" />
  <link rel="stylesheet" href="www/css/lib/bootstrap-1.3.0.min.css" />
  <link rel="stylesheet" href="www/css/cintient.css">
  <link rel="stylesheet" href="www/css/installer.css" />
  <link rel="icon" href="www/imgs/favicon.ico">
  <link rel="apple-touch-icon" href="www/imgs/favicon.ico" />
  <!--[if lt IE 9]>
  <script src="www/js/lib/html5.js"></script>
  <![endif]-->
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="www/js/lib/jquery-1.7.min.js"></script>
  <script type="text/javascript" src="www/js/lib/bootstrap/bootstrap-alerts.js"></script>
  <script type="text/javascript" src="www/js/cintient.js"></script>
  <script type="text/javascript" src="www/js/installer.js"></script>
</head>
<body>

  <div id="welcomeScreen" title="Welcome screen" class="nodisplay">
    <div class="cintientLettering">Cintient</div>
    <div class="greetings" style="display: none;"><?php echo $greetings[rand(0, count($greetings)-1)]; ?></div>
  </div>

  <div id="installer" class="nodisplay">
    <div class="topbar">
      <div class="fill">
        <div class="container">
          <ul class="nav">
            <li><div class="cintientLettering" id="logoLettering">Cintient</div></li>
          </ul>
        </div>
      </div>
    </div>

    <div id="alertPane"></div>

    <div class="container">
      <div class="mainContent">
        <div class="page-header">
          <h1></h1>
        </div>
        <div id="sectionContent">

          <div id="step-0" class="installerStep" title="Minimum requirements">
            <form action class="form-stacked">
              <fieldset>
<?php
list ($ok, $msg) = phpInstallationVersion();
?>
                <div class="item clearfix<?php echo ($ok ? ' success' : ' fail'); ?>">
                  <label for="phpInstallationVersion">PHP 5.3.x</label>
                  <div id="phpInstallationVersion" class="input">
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                </div>
<?php
list ($ok, $msg) = phpWithSqlite();
?>
                <div class="item clearfix<?php echo ($ok ? ' success' : ' fail'); ?>">
                  <label for="phpWithSqlite">PHP with SQLite3</label>
                  <div id="phpWithSqlite" class="input">
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                </div>
<?php
list ($ok, $msg) = modRewrite();
?>
                <div class="item clearfix<?php echo ($ok ? ' success' : ' fail'); ?>">
                  <label for="modRewrite">Apache, Lighttpd or NGiNX</label>
                  <div id="modRewrite" class="input">
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                </div>
                <div class="actions"><span id="actionNext"></span></div>
              </fieldset>
            </form>
          </div>


          <div id="step-1" class="installerStep" title="Basic setup">
            <form action class="form-stacked">
              <fieldset>
<?php
list ($ok, $msg) = appWorkDir($defaults['appWorkDir']);
?>
                <div class="item clearfix inputCheckOnChange<?php echo ($ok ? ' success' : ' fail'); ?>" id="appWorkDir">
                  <label for="appWorkDir">Data dir</label>
                  <div class="input">
                    <input class="span6" type="text" name="appWorkDir" value="<?php echo $defaults['appWorkDir']; ?>" />
                    <span class="help-inline">An update will be performed instead, if a previous installation is detected.</span>
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                  <!--div class="help-block">Place for working files and databases, independent from the installation dir.</div-->
                </div>
<?php
list ($ok, $msg) = baseUrl($defaults['baseUrl']);
?>
                <div class="item clearfix inputCheckOnChange<?php echo ($ok ? ' success' : ' fail'); ?>" id="baseUrl">
                  <label for="baseUrl">Base URL where Cintient will run from</label>
                  <div class="input">
                    <input class="span6" type="text" name="baseUrl" value="<?php echo $defaults['baseUrl']; ?>" />
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                  <!--div class="help-block">Cintient tried to guess it. If you are not sure, just go with its suggestion.</div-->
                </div>
<?php
list ($ok, $msg) = htaccessFile($defaults['htaccessFile']);
?>
                <div class="item clearfix inputCheckOnChange<?php echo ($ok ? ' success' : ' fail'); ?>" id="htaccessFile">
                  <label for="htaccessFile"><?php if ($report['server'] == 'apache') { ?>.htaccess<?php } else if ($report['server'] == 'lighttpd') { ?>cintient-lighttpd.conf<?php } else if ($report['server'] == 'nginx') { ?>cintient-nginx.conf<?php } ?></label>
                  <div class="input">
                    <input class="span6" type="text" name="htaccessFile" value="<?php echo $defaults['htaccessFile']; ?>" disabled="disabled" />
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                </div>
<?php
list ($ok, $msg) = configurationDir($defaults['configurationDir']);
?>
                <div class="item clearfix inputCheckOnChange<?php echo ($ok ? ' success' : ' fail'); ?>" id="configurationDir">
                  <label for="configurationDir">Configuration dir</label>
                  <div class="input">
                    <input class="span6" type="text" name="configurationDir" value="<?php echo $defaults['configurationDir']; ?>" disabled="disabled" />
                    <span class="help-block"><?php echo $msg; ?></span>
                  </div>
                </div>
                <div class="actions"><span id="actionNext"></span></div>
              </fieldset>
            </form>
          </div>

          <div id="step-2" class="installerStep" title="Administration account">
            <form action class="form-stacked">
              <fieldset>
                <div class="item clearfix inputCheckOnChange fail freshOnly" id="email">
                  <label for="email">Email</label>
                  <!--div class="fineprintLabel">(for administration notifications)</div-->
                  <div class="input">
                    <input class="span6" type="text" name="email" value="" />
                    <span class="help-block">Email field is empty or invalid.</span>
                  </div>
                </div>
                <div class="item clearfix inputCheckOnChange fail" id="password">
                  <label for="password">Password</label>
                  <div class="input">
                    <input class="span6" type="password" name="password" value="" />
                    <span class="help-inline upgradeOnly">You are performing an update. Authenticate yourself as root, to continue.</span>
                  </div>
                  <label for="passwordr">Re-type password</label>
                  <div class="input">
                    <input class="span6" type="password" name="passwordr" value="" />
                    <span class="help-block">Passwords are empty.</span>
                  </div>
                </div>
                <div class="actions"><span id="actionNext"></span></div>
              </fieldset>
            </form>
          </div>
        </div>
      </div>

      <footer>
        <p>Cintient is free software distributed under the GNU General Public License version 3 or later terms.</p>
      </footer>
    </div>
  </div>

  <div id="finished" class="nodisplay">
    <h1>Almost there, just a few more seconds...</h1>
    <div><img src="www/imgs/loading-3.gif" /></div>
  </div>

<script type="text/javascript">
// <![CDATA[
//inputLocalCheckOnChange validation function
function inputCheckOnChangeEmail()
{
  var input = $("#step-2 #email input").val();
  var msg = ['Email field is empty or invalid', 'Ready.'];
  var ok = (input.length > 1);
  return {ok: ok, msg: msg[Number(ok)]}; // TODO: check email better
}
//inputLocalCheckOnPassword validation function
function inputCheckOnChangePassword()
{
  var input1 = $('#step-2 #password input[name="password"]').val();
  var input2 = $('#step-2 #password input[name="passwordr"]').val();
  var msg = ["Passwords are empty or don't match.", 'Ready.'];
  var ok = (input1.length > 0 && input1 == input2);
  return {ok: ok, msg: msg[Number(ok)]};
}
$(document).ready(function() {
  CintientInstaller.init();
});
// ]]>
</script>
</body>
</html>