<?php
//
// Check for PHP support.
//
if (false) {
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title>Cintient installation halted!</title>
  <link rel="icon" href="/favicon.ico">
  <meta name="generator" content="Cintient Engine" />
  <style type="text/css">
    .error {color:red;font-weight:bold;}
  </style>
</head>
<body>
  <h1>Cintient</h1>
  <h2><span class="error">Error:</span> PHP environment not found!</h2>
  <p>Cintient requires a <a href="http://php.net">PHP</a> environment in order to run.</p>
  <p>Please install PHP and try again.</p>
</body>
</html>
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
 *  the end of this very script, if every pre-requisite is satisfied.
 *
 *  @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 *
 */

//
// Try to check if we already have our own document root setup, i.e.,
// a vhost (from a previous install). Adapt relative URIs accordingly.
//
$uriPrefix = '';
if (!preg_match('/(.+\/www)\/?$/', $_SERVER['DOCUMENT_ROOT'], $matches) ||
     dirname(__FILE__) . '/www' != $matches[1]
) {
  $uriPrefix = 'www/';
}
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $uriPrefix;
if (($pos = strpos($baseUrl, '?')) !== false) { // Remove the query
  $baseUrl = substr($baseUrl, 0, $pos);
}

/**
 * Following are default values for the installation script
 */
$defaults = array();
$defaults['baseUrl'] = $baseUrl;
$defaults['appWorkDir'] = '/var/run/cintient/';


/**
 * Following are function definitions for all items
 */
function apacheModRewrite()
{
  $msg[0] = "Apache mod_rewrite is required.";
  $msg[1] = "Detected.";
  $ok = in_array("mod_rewrite", apache_get_modules());
  return array($ok, $msg[(int)$ok]);
}

function phpInstallationVersion()
{
  $msg[0] = "Version 5.3.3 or higher is required.";
  $msg[1] = "Detected version " . phpversion() . ".";
  $ok = version_compare(phpversion(), '5.3.3', '>=');
  return array($ok, $msg[(int)$ok]);
}

function phpWithSqlite()
{
  $msg[0] = "PHP with sqlite3 version 2.5 or higher required.";
  $msg[1] = "Detected version " . sqlite_libversion() . ".";
  $ok = extension_loaded('sqlite3') && sqlite_libversion() > '2.5';
  return array($ok, $msg[(int)$ok]);
}

function phpWithGd()
{
  $gdInfo = gd_info();
  $msg[0] = "PHP with GD (with PNG and FreeType support) is required.";
  $msg[1] = "Detected version " . $gdInfo['GD Version'] . ".";
  $ok = ((isset($gdInfo['PNG Support']) && $gdInfo['PNG Support'] !== false)) &&
        ((isset($gdInfo['FreeType Support']) && $gdInfo['FreeType Support'] !== false));
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
  $msg[0] = "Check that the path exists and that your webserver has write permissions there.";
  $msg[1] = "Ready.";
  if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
    $dir .= DIRECTORY_SEPARATOR;
  }
  $testDir = $dir . '.install';
  $ok = (@mkdir($testDir, 0777, true) != false);
  @rmdir($testDir);
  return array($ok, $msg[(int)$ok]);
}

//
// AJAX checks
//
if (!empty($_GET['c'])) {
  $ok = false;
  $msg = 'Invalid request';
  // TODO: Sanatize input
  $c = $_GET['c'];
  $v = (empty($_GET['v'])?null:$_GET['v']);
  if (function_exists($c)) {
    list($ok, $msg) = $c($v);
  }
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

  /*


  //
  // save config file
  //
  $installDir         = '';
  $configFileLocation = '';
  $content            = "";
  foreach ($_POST as $key => $value) {
    if (strpos($key, 'CONFIG_VAR') !== false) {
      $cKey = str_replace('CONFIG_VAR-' , '' , $key);
      if ($cKey == 'CINTIENT_INSTALL_DIR') {
        $installDir = $value;
        $configFileLocation = $value . "/src/config/cintient.conf.php";
      }
      $content .= "define('".$cKey."', '".$value."');\n";
    }
  }

  // test xml install file one more time
  $fpThis = fopen(__FILE__, "r");
  fseek($fpThis, __COMPILER_HALT_OFFSET__);
  $configVars = new SimpleXMLElement(stream_get_contents($fpThis), LIBXML_NOCDATA);
  fclose($fpThis);
  if (!$configVars instanceOf SimpleXMLElement) {
    die("ERROR: Could not load installation file!");
  }

  // add xml config vars
  foreach ($configVars->config->var as $configVar) {
    $configVarName        = $configVar['name'];
    eval("\$configVarValue=".(string)$configVar.";");
    $content .= "define('{$configVarName}', {$configVarValue});\n";
  }

  // add autoloader
  $content .= (string)$configVars->autoload;
  $fp = fopen($configFileLocation, 'w');
  fwrite($fp, "<?php\n".$content);
  fclose($fp);

  //
  // save htaccess file
  //
  $file = 'www/.htaccess';
  $fp   = fopen($file, 'w');
  if ($fp) {
    fwrite($fp, "RewriteEngine on\n");
    fwrite($fp, "RewriteRule ^/ajax/ /ajaxHandler.php [L]\n");
    fwrite($fp, "RewriteRule ^(?!/js/)(?!/css/)(?!/imgs/)(?!/fonts/)(.*)$ /webHandler.php [L]\n");
    fwrite($fp, "php_value include_path " . $installDir);
    fclose($fp);
  }

  // try to fix permissions
  // TODO: warn the user about this
  //       permissions on .htaccess and config file
  //       should have the right ones after installation
  chmod('.htaccess',         0755);
  chmod($configFileLocation, 0755);

  // install user admin info
  eval($content); # now we can use config settings
  $userName     = "";
  $userEmail    = "";
  $userUsername = "";
  $userPassword = "";
  $passwordTest = "";
  foreach ($_POST as $key => $value) {
    if (strpos($key, 'DATABASE_VAR') !== false) {
      $cKey = str_replace('DATABASE_VAR-' , '' , $key);
      switch ($cKey) {
        case 'NAME'            : $userName = $value; break;
        case 'EMAIL'           : $userEmail = $value; break;
        case 'USERNAME'        : $userUsername = $value; break;
        case 'PASSWORD'        : $userPassword = $value; break;
        case 'PASSWORD_REPEAT' : $passwordTest = $value; break;
        default: break;
      }
    }
  }

  // TODO: add a 'nicer' message here
  if ($userPassword !== $passwordTest) {
    die('Please double check your password again!');
  }

  // install admin user
  // TODO: run all Classes 'install' method dynamically here
  User::install();    # create user table
  Project::install(); # creat project table
  $user = new User();
  $user->setEmail($userEmail);
  $user->setNotificationEmails($userEmail); # TODO: this should allow even more emails to add to this list
  $user->setName($userName);
  $user->setUsername($userUsername);
  $user->setCos(UserCos::ROOT);
  $user->init();
  $user->setPassword($userPassword);

  header('Location: ' . UrlManager::getForDashboard());
  exit;
  */
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
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_anonymouspro.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_orbitron.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_syncopate.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/global.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/installer.css" />
  <link rel="icon" href="<?php echo $uriPrefix; ?>favicon.ico">
  <!--[if lt IE 9]>
  <script src="<?php echo $uriPrefix; ?>js/lib/html5.js"></script>
  <![endif]-->
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="<?php echo $uriPrefix; ?>js/lib/jquery-1.6.js"></script>
  <script type="text/javascript" src="<?php echo $uriPrefix; ?>js/installer.js"></script>
</head>
<body id="installer">
  <div id="splashHeader" class="container">
    <header>
      <hgroup>
        <h1 style="display: none;">Cintient</h1>
        <img style="display: none;" src="<?php echo $uriPrefix; ?>imgs/redhalo.jpg" width="195" height="130">
      </hgroup>
    </header>
    <div class="greetings" style="display: none;"><?php echo $greetings[rand(0, count($greetings)-1)]; ?></div>
  </div>
  <div id="header" class="containerTopLevel">
    <div id="userHeader" class="container">
      <header>
        <hgroup>
          <h1 id="logo" style="display: none;">Cintient <img src="<?php echo $uriPrefix; ?>imgs/redhalo_45.jpg" height="25"></h1>
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
list ($ok, $msg) = apacheModRewrite();
?>
          <li id="apacheModRewrite">
            <div class="label">Apache mod_rewrite</div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
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
            <div class="label">PHP compiled with SQLite3 2.5.x</div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = phpWithGd();
?>
          <li id="phpWithGd">
            <div class="label">PHP compiled with GD</div>
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
            <div class="fineprintLabel">(we tried to automatically guess it. If you are not sure, just go with our suggestion)</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" type="text" name="baseUrl" value="<?php echo $defaults['baseUrl']; ?>" style="width: 450px;" /></div>
            <div class="result <?php echo ($ok ? 'success' : 'error'); ?>"><?php echo $msg; ?></div>
          </li>
<?php
list ($ok, $msg) = appWorkDir($defaults['appWorkDir']);
?>
          <li class="inputCheckOnChange" id="appWorkDir">
            <div class="label">Application work files directory</div>
            <div class="fineprintLabel">(the place for work files and databases, different from the installation directory)</div>
            <div class="textfieldContainer" style="width: 456px;"><input class="textfield" style="width: 450px;" type="text" name="appWorkDir" value="<?php echo $defaults['appWorkDir']; ?>" /></div>
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
  return {ok: ok, msg: msg[Number(ok)]}; // TODO: check email better
}
$(document).ready(function() {
  new Installer({step:0});
});
// ]]>
</script>
</body>
</html>