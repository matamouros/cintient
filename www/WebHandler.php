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



/* +----------------------------------------------------------------+ *\
|* | EARLY SANATIZATION                                             | *|
\* +----------------------------------------------------------------+ */
//
// Nothing works until the installation file is out of the way.
//
if (file_exists(dirname(__FILE__) . '/../index.php')) {
  //include dirname(__FILE__) . '/../index.php';
  //exit;
}
//
// Proxy layer compatibility hack, if there is one.
//
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
//
// Clickjacking protection
//
header('X-Frame-Options: DENY');
header('Content-type: text/html; charset=UTF-8');



/* +----------------------------------------------------------------+ *\
|* | SETUP                                                          | *|
\* +----------------------------------------------------------------+ */

require 'src/config/cintient.conf.php';
require 'lib/Smarty-3.0rc4/Smarty.class.php';

session_start(); // session_start *has* to come after the custom autoloading

#if DEBUG
SystemEvent::raise(SystemEvent::DEBUG, "Handling request. [URI={$_SERVER['SCRIPT_URL']}" . (empty($_SERVER['QUERY_STRING'])?'':'?'.html_entity_decode($_SERVER['QUERY_STRING'])) . "]", __METHOD__);
#endif

//$_SESSION['language'] = null;
//$_SESSION['project'] = null; // The active Project object or null, *always and only*
//$_SESSION['user']; The logged-in User object or null, *always and only*
//
// Volatile stuff
//
$GLOBALS['section'] = null;
$GLOBALS['smarty'] = null;
$GLOBALS['subSection'] = null;
$GLOBALS['templateFile'] = null;
$GLOBALS['templateMethod'] = null;
$GLOBALS['uri'] = $_SERVER['SCRIPT_URL'] . (substr($_SERVER['SCRIPT_URL'], -1) != '/' ? '/' : '');
//
// Smarty
//
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->setAllowPhpTag(true);
$GLOBALS['smarty']->setCacheLifetime(0);
$GLOBALS['smarty']->setDebugging(SMARTY_DEBUG);
$GLOBALS['smarty']->setForceCompile(SMARTY_FORCE_COMPILE);
$GLOBALS['smarty']->setCompileCheck(SMARTY_COMPILE_CHECK);
$GLOBALS['smarty']->setTemplateDir(SMARTY_TEMPLATE_DIR);
$GLOBALS['smarty']->setCompileDir(SMARTY_COMPILE_DIR);
$GLOBALS['smarty']->error_reporting = error_reporting();



/* +----------------------------------------------------------------+ *\
|* | URL HANDLING                                                   | *|
\* +----------------------------------------------------------------+ */

if (preg_match('/^\/(?:([\w-]+)\/(?:([\w-]+)\/)?)?$/', $GLOBALS['uri'], $matches)) {
  if (count($matches) == 1) {
    $GLOBALS['section'] = 'default';
    $GLOBALS['subSection'] = 'dashboard';
  } elseif (count($matches) == 2) {
    $GLOBALS['section'] = 'default';
    $GLOBALS['subSection'] = $matches[1];
  } else {
    $GLOBALS['section'] = $matches[1];
    $GLOBALS['subSection'] = $matches[2];
  }
}



/* +----------------------------------------------------------------+ *\
|* | AUTHENTICATION                                                 | *|
\* +----------------------------------------------------------------+ */

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
  if ((!Auth::authenticate() || !($_SESSION['user'] instanceof User)) && $GLOBALS['subSection'] != 'install') {
    //
    // Special case of template logic here, because the URI will get overwritten
    // right after it. Somewhere, a cute small kitten died a horrible death.
    //
    $GLOBALS['smarty']->assign('authentication_redirectUri', urlencode($GLOBALS['uri']));
    $GLOBALS['subSection'] = 'authentication';
    $_SESSION['user'] = null;
  }
}



/* +----------------------------------------------------------------+ *\
|* | ROUTING                                                        | *|
\* +----------------------------------------------------------------+ */

if (!empty($GLOBALS['section'])) {
  $GLOBALS['templateFile'] = $GLOBALS['subSection'] . '.tpl';
  $GLOBALS['templateMethod'] = $GLOBALS['subSection'];
  if (strpos($GLOBALS['subSection'], '-') !== false) {
    $subSectionPieces = explode('-', $GLOBALS['subSection']);
    array_walk($subSectionPieces, function(&$value) {
      $value = ucfirst($value);
    });
    $GLOBALS['templateMethod'] = lcfirst(implode($subSectionPieces));
  }
  if ($GLOBALS['section'] != 'default') {
    $GLOBALS['templateFile'] = $GLOBALS['section'] . '/' . $GLOBALS['subSection'] . '.tpl';
    $GLOBALS['templateMethod'] = $GLOBALS['section'] . '_' . $GLOBALS['templateMethod'];
  }
  if (method_exists('TemplateManager', $GLOBALS['templateMethod'])) {
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Routing to known template function. [FUNCTION=TemplateManager::{$GLOBALS['templateMethod']}] [URI={$GLOBALS['uri']}]", __METHOD__);
    #endif
    TemplateManager::$GLOBALS['templateMethod']();
    $GLOBALS['smarty']->assign('globals_subSection', $GLOBALS['subSection']);
    $GLOBALS['smarty']->display($GLOBALS['templateFile']);
    exit;
  }
  #if DEBUG
  SystemEvent::raise(SystemEvent::DEBUG, "Unknown template function. [FUNCTION=TemplateManager::{$GLOBALS['templateMethod']}] [URI={$GLOBALS['uri']}]", __METHOD__);
  #endif
}



/* +----------------------------------------------------------------+ *\
|* | RESTAURANT AT THE END OF THE UNIVERSE                          | *|
\* +----------------------------------------------------------------+ */

Redirector::redirectAndExit(Redirector::NOT_FOUND);
