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
// Proxy layer compatibility hack, if there is one.
//
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}



/* +----------------------------------------------------------------+ *\
|* | SETUP                                                          | *|
\* +----------------------------------------------------------------+ */

require dirname(__FILE__) . '/../config/cintient.conf.php';
ini_set('display_errors', 0); // Don't echo errors on AJAX calls
SystemEvent::setSeverityLevel(CINTIENT_LOG_SEVERITY);

//
// Global stuff
//
// Get to the part of the URL that matters
$currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
$GLOBALS['uri'] = substr($currentUrl, strlen(CINTIENT_BASE_URL));

SystemEvent::raise(SystemEvent::DEBUG, "Handling request. [URI={$GLOBALS['uri']}" . (empty($_SERVER['QUERY_STRING'])?'':'?'.html_entity_decode($_SERVER['QUERY_STRING'])) . "]", "ajaxHandler");
$GLOBALS['ajaxMethod'] = null;
$GLOBALS['section'] = null;
$GLOBALS['settings'] = SystemSettings::load(); // Pull up system settings
$GLOBALS['subSection'] = null;
session_start();
$GLOBALS['user'] = (isset($_SESSION['userId']) ? User::getById($_SESSION['userId']) : null);
$GLOBALS['project'] = ((!empty($_SESSION['projectId']) || !empty($_REQUEST['pid'])) && !empty($GLOBALS['user']) ? Project::getById($GLOBALS['user'], (!empty($_REQUEST['pid'])?$_REQUEST['pid']:$_SESSION['projectId'])) : null);
$_SESSION['projectId'] = ($GLOBALS['project'] instanceof Project ? $GLOBALS['project']->getId() : null);



/* +----------------------------------------------------------------+ *\
|* | URL HANDLING                                                   | *|
\* +----------------------------------------------------------------+ */

//
// Ajax related
//
if (preg_match('/^\/ajax\/([\w-]+)(?:\/([\w-]+))?\/$/', $GLOBALS['uri'], $matches)) {
  if (count($matches) <= 2) {
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

if (!isset($GLOBALS['user']) || !($GLOBALS['user'] instanceof User)) {
  if ((!Auth::authenticate() || !($GLOBALS['user'] instanceof User)) &&
      ($GLOBALS['subSection'] != 'authentication' &&
      ($GLOBALS['subSection'] != 'registration' || $GLOBALS['settings'][SystemSettings::ALLOW_USER_REGISTRATION])))
  {
    SystemEvent::raise(SystemEvent::INFO, "Authentication is required on all ajax requests. [URI={$GLOBALS['uri']}]", "ajaxHandler");
    // TODO: send error here
    exit;
  }
}
session_write_close();



/* +----------------------------------------------------------------+ *\
|* | ROUTING                                                        | *|
\* +----------------------------------------------------------------+ */

//
// Ajax related
//
if (!empty($GLOBALS['section'])) {
  $GLOBALS['ajaxMethod'] = $GLOBALS['subSection'];
  if (strpos($GLOBALS['subSection'], '-') !== false) {
    $subSectionPieces = explode('-', $GLOBALS['subSection']);
    array_walk($subSectionPieces, function(&$value) {
      $value = ucfirst($value);
    });
    $GLOBALS['ajaxMethod'] = lcfirst(implode($subSectionPieces));
  }
  if ($GLOBALS['section'] != 'default') {
    $GLOBALS['ajaxMethod'] = $GLOBALS['section'] . '_' . $GLOBALS['ajaxMethod'];
  }
  if (method_exists('AjaxManager', $GLOBALS['ajaxMethod'])) {
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Routing to known ajax function. [FUNCTION=AjaxManager::{$GLOBALS['ajaxMethod']}] [URI={$GLOBALS['uri']}]", "ajaxHandler");
    #endif
    AjaxManager::$GLOBALS['ajaxMethod']();
    exit;
  }
  #if DEBUG
  SystemEvent::raise(SystemEvent::DEBUG, "Unknown ajax function. [FUNCTION=AjaxManager::{$GLOBALS['ajaxMethod']}] [URI={$GLOBALS['uri']}]", "ajaxHandler");
  #endif
}



/* +----------------------------------------------------------------+ *\
|* | RESTAURANT AT THE END OF THE UNIVERSE                          | *|
\* +----------------------------------------------------------------+ */

SystemEvent::raise(SystemEvent::INFO, "Not found. [URI={$GLOBALS['uri']}] [USER=" . (($GLOBALS['user'] instanceof User)? $GLOBALS['user']->getUsername() : 'N/A') . ']');
// TODO: send error here
exit;
