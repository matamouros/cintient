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
 *  
 *  This is Cintient's installation script. The only way to make it past
 *  installation is to delete this and boot.xml, which should be done at
 *  the end of this very script, if every pre-requisite is satisfied.
 *  
 *  @author Pedro Eugenio <voxmachina@gmail.com>
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

//
// Check for php support
//
if (false) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error: PHP is not running</title>
	<style type="text/css">
    .error {color:red;font-weight:bold;}
	</style>
</head>
<body>
	<h1>Cintient</h1>
	<h2><span class="error">Error:</span> PHP is not running</h2>
	<p>You'll need <a href="http://php.net">php</a> in order to start using Cintient</p>
	<p>Be sure to fulfill the <a href="#">requirements</a></p>
</body>
</html>
<?php
exit;
}

// [optional] 
// Give this key to user in order to check the veracity of installation file
// User can check the file using: 'shasum boot.xml' 
// $key = hash_file('sha1', 'boot.xml');

//
// check for POST data
//
if (isset($_POST) && count($_POST) > 0) {
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
  $configVars = new SimpleXMLElement(dirname(__FILE__) . '/boot.xml', LIBXML_NOCDATA, true);
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
    fwrite($fp, "RewriteRule ^(?!/js/)(.*)$ WebHandler.php [L]\n");
    fwrite($fp, "php_value include_path '.:".$installDir."/'");
    fclose($fp);
  }
  
  // try to fix permissions
  // TODO: warn the user about this
  //       permissions on .htaccess and config file
  //       should have the right ones after installation
  chmod('.htaccess',         755);
  chmod($configFileLocation, 755);
  
  // install user admin info
  eval($content); /* now we ca use config settings */
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
  User::install();    /* create user table */
  Project::install(); /* creat project table */
  $user = new User();
  $user->setEmail($userEmail);
  $user->setNotificationEmails($userEmail); /* TODO: this should allow even more emails to add to this list */
  $user->setName($userName);
  $user->setUsername($userUsername);
  $user->setCos(UserCos::ROOT);
  $user->init();
  $user->setPassword($userPassword);
  
  header('Location: ' . URLManager::getForDashboard());
  exit;
}

//
// Check SimpleXML and libxml support needed to read settings file
//
if (!extension_loaded('SimpleXML') || !extension_loaded('libxml')) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error: PHP is not running with SimpleXML and libxml Support</title>
	<style type="text/css">
    .error {color:red;font-weight:bold;}
	</style>
</head>
<body>
	<h1>Cintient</h1>
	<h2><span class="error">Error:</span> PHP is not running</h2>
	<p>You'll need <a href="http://php.net">php</a> with SimpleXML in order to start using Cintient</p>
	<p>Be sure to fulfill the <a href="#">requirements</a></p>
</body>
</html>
<?php
exit;
}

//
// Try to read settings file
//
$settings = new SimpleXMLElement(dirname(__FILE__) . '/boot.xml', LIBXML_NOCDATA, true);
if (!$settings instanceOf SimpleXMLElement) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error: Could not load settings file</title>
	<style type="text/css">
    .error {color:red;font-weight:bold;}
	</style>
</head>
<body>
	<h1>Cintient</h1>
	<h2><span class="error">Error:</span>Could not load settings file, check for file: 'boot.xml' at the web project root</h2>
</body>
</html>
<?php
exit;
}
$greetings = array(
  'Greetings human.',
  'Hello, Dave!',
  "They'll fix you. They fix everything.",
  'Looking for me?',
  'Stay out of trouble.',
  "This will all end in tears.",
);
//
// Ok ready to start installation!
//
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Cintient Installation</title>
	<link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_anonymouspro.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_orbitron.css" />
  <link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/font_syncopate.css" />
	<link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/global.css" />
	<link rel="stylesheet" href="<?php echo $uriPrefix; ?>css/installer.css" />
	<script type="text/javascript" src="<?php echo $uriPrefix; ?>js/jquery-1.4.4.js"></script>
	<script type="text/javascript" src="<?php echo $uriPrefix; ?>js/installer.js"></script>
</head>
<body id="installer">
  <div id="splashHeader" class="container">
<?php
if (!isset($_GET['step'])) {
?>
    <header>
      <hgroup>
        <h1>Cintient</h1>
        <img src="/imgs/redhalo.jpg" width="195" height="130">
      </hgroup>
    </header>
    <div class="greetings"><?php echo $greetings[rand(0, count($greetings)-1)]; ?></div>
  
<script type="text/javascript">
// <![CDATA[
$('#splashHeader h1').hide();
$('#splashHeader img').hide();
$('#splashHeader .greetings').hide();
$(document).ready(function() {
  $('#splashHeader h1').fadeIn(300);
  $('#splashHeader img').fadeIn(300);
  setTimeout(
		function() {
	    $('#splashHeader .greetings').fadeIn(1000);
		},
		1000
  );
});
// ]]> 
</script>
<?php
}
?>
  </div>
  <div class="containerTopLevel">
    <div id="mainMenu">
      <ul>
        <li id="historyBack"><?php
$stepNumber = 1;
if (isset($_GET['step'])) {
  $stepNumber = $_GET['step'];
}
for ($i=0; $i<count($settings->step); $i++) {
  if ($stepNumber != $i+1) {
?><span class="step-<?php echo $i+1; ?> ghosted">&#8226;</span><?php
  } else {
?><span class="step-<?php echo $i+1; ?>">&#8226;</span><?php
  }
}
?></li>
        <li id="sectionName"><?php
if (!isset($_GET['step']) || !isset($settings->step[$_GET['step']-1])) {
  $stepNumber = 1;
}
echo $settings->step[$stepNumber-1]["description"];
?></li>
      </ul>
    </div>
<script type="text/javascript">
// <![CDATA[
$('#mainMenu').hide();
// ]]> 
</script>
  </div>
  <div class="containerTopLevel">
	<form method="post" id="install" name="install" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php foreach ($settings->step as $step) : ?>
  <div id="step-<?php echo $step['number']; ?>" class="installer_step hidden container">
    <div>
  <?php foreach ($step->item as $item) : ?>
  	<ul class="item">
    <?php foreach ($item->items->item as $element) : ?>
  	  <li class="element">
      <?php
      $hasInputs = false;
      if (isset($element->inputs)) {
       $hasInputs = true;
      }
    	?>
        <div class="label"><?php echo (string)$element['label']; ?></div>
      <?php 
      if ($hasInputs) {
        foreach ($element->inputs as $input) {
          eval("\$out=".(string)$input->input);
          echo $out;
        }
      }
      $retAll = true;
      foreach ($element->methods->method as $method) {
        eval("\$retAll=\$retAll && ".(string)$method);
      }
      
      if ($retAll) : ?>
      <?php eval("\$msg=".(string)$element->messages->success); ?>
        <div class="success"><?php echo $msg; ?></div>
      <?php else : ?>
      <?php eval("\$msg=".(string)$element->messages->error); ?>
        <div class="error"><?php echo $msg; ?></div>
      <?php endif; ?>
  	  </li>
  	<?php endforeach; ?>
  	</ul>

<script type="text/javascript">
// <![CDATA[
$('#step-<?php echo $step['number']; ?>').hide();
// ]]>
</script>
  <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
  <div class="container">
    <input type="submit" class="hidden submitButton" name="save" id="save" value="Next" />
  </div>
<script type="text/javascript">
// <![CDATA[
$('#save').hide();
// ]]>
</script>
  </form>
  </div>
<?php
$index = 0;
if (isset($_GET['step'])) {
  $index = (int)$_GET['step']-1;
?>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	$('#mainMenu').fadeIn(500);
  new Installer({index:<?php echo $index; ?>});
});
// ]]>
</script>
<?php
} else {
?>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  setTimeout(
		function() {
			$('#splashHeader .greetings').fadeOut(100);
			$('#splashHeader h1').fadeOut(500);
			$('#splashHeader img').fadeOut(500);
			setTimeout(
				function(){
					$('#mainMenu').fadeIn(500);
				  new Installer({index:<?php echo $index; ?>});
			  },
			  700
			);
	  },
	  4000
  );
});
// ]]>
</script>
<?php
}
?>
</body>
</html>