<?php
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



/* +----------------------------------------------------------------+ *\
|* | EARLY SANATIZATION                                             | *|
\* +----------------------------------------------------------------+ */
//
// Nothing works until the installation file is out of the way.
//
if (file_exists(dirname(__FILE__) . '/../../index.php')) {
  //include dirname(__FILE__) . '/../../index.php';
  //exit;
}
//
// Proxy layer compatibility hack, if there is one.
//
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}



/* +----------------------------------------------------------------+ *\
|* | SETUP                                                          | *|
\* +----------------------------------------------------------------+ */

ob_start();
header('Content-type: text/html; charset=UTF-8');

require dirname(__FILE__) . '/../config/cintient.conf.php';
require CINTIENT_SMARTY_INCLUDE;

session_start(); // session_start *has* to come after the custom autoloading
SystemEvent::setSeverityLevel(CINTIENT_LOG_SEVERITY);

//
// Global stuff
//
// Get to the part of the URL that matters
$currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
$GLOBALS['uri'] = substr($currentUrl, strlen(CINTIENT_BASE_URL));
if (substr($GLOBALS['uri'], -1) != '/') {
  $GLOBALS['uri'] .= '/';
}
SystemEvent::raise(SystemEvent::DEBUG, "Handling request. [URI={$GLOBALS['uri']}" . (empty($_SERVER['QUERY_STRING'])?'':'?'.html_entity_decode($_SERVER['QUERY_STRING'])) . "]", "WebHandler");
$GLOBALS['section'] = null;
$GLOBALS['settings'] = SystemSettings::load(); // Pull up system settings
$GLOBALS['smarty'] = null;
$GLOBALS['subSection'] = null;
$GLOBALS['templateFile'] = null;
$GLOBALS['templateMethod'] = null;
$GLOBALS['user'] = (isset($_SESSION['userId']) ? User::getById($_SESSION['userId']) : null);
$GLOBALS['project'] = ((!empty($_SESSION['projectId']) || !empty($_GET['pid'])) && !empty($GLOBALS['user']) ? Project::getById($GLOBALS['user'], (!empty($_GET['pid'])?$_GET['pid']:$_SESSION['projectId'])) : null);
$_SESSION['projectId'] = ($GLOBALS['project'] instanceof Project ? $GLOBALS['project']->getId() : null);
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
Framework_SmartyPlugin::init($GLOBALS['smarty']);



/* +----------------------------------------------------------------+ *\
|* | POST-SETUP                                                     | *|
\* +----------------------------------------------------------------+ */

if ($GLOBALS['settings'][SystemSettings::INTERNAL_BUILDER_ACTIVE]) {
  include 'src/handlers/buildHandler.php';
}



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

if ((!isset($GLOBALS['user']) || !($GLOBALS['user'] instanceof User)) &&
  ($GLOBALS['subSection'] != 'registration' || !$GLOBALS['settings'][SystemSettings::ALLOW_USER_REGISTRATION]))
{
  SystemEvent::raise(SystemEvent::INFO, "Authentication required. [URI={$GLOBALS['uri']}]", "webHandler");
  //
  // Special case of template logic here, because the URI will get overwritten
  // right after it. Somewhere, a cute small kitten died a horrible death.
  //
  if (strlen($GLOBALS['uri']) > 1) { // Don't redirect the root URI (/)
    $GLOBALS['smarty']->assign('authentication_redirectUri', urlencode($GLOBALS['uri']));
  }
  $GLOBALS['section'] = 'default';
  $GLOBALS['subSection'] = 'authentication';
  $GLOBALS['user'] = null;
  $_SESSION['userId'] = null;
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
    $GLOBALS['smarty']->assign('globals_settings', $GLOBALS['settings']);
    $GLOBALS['smarty']->assign('globals_section', $GLOBALS['section']);
    $GLOBALS['smarty']->assign('globals_subSection', $GLOBALS['subSection']);
    $GLOBALS['smarty']->assign('globals_user', $GLOBALS['user']);
    $GLOBALS['smarty']->assign('globals_project', $GLOBALS['project']);
    ob_end_clean();
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
