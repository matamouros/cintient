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
// if (file_exists(dirname(__FILE__) . '/../index.php')) {
// 	include dirname(__FILE__) . '/../index.php';
// 	exit;
// }



/* +----------------------------------------------------------------+ *\
|* | BOOTSTRAP                                                      | *|
\* +----------------------------------------------------------------+ */

ob_start();
header('Content-type: text/html; charset=UTF-8');

// Sentient Framework
include '../lib/sentient/bootstrap.php';
$GLOBALS['config'] = new Sentient\Config('../config/');
$GLOBALS['config']->init();

// Register Cintient's autoloader
spl_autoload_register(function ($classname) {
	if (strpos($classname, '_') !== false) {
		$classname = str_replace('_', '/', $classname);
	}
	if (is_file('../src/core/' . $classname . '.php')) {
		include '../src/core/' . $classname . '.php';
	}
});

session_start(); // session_start *has* to come after the custom autoloading

SystemEvent::setSeverityLevel($GLOBALS['config']->valueForKey('log.severity'));

// Temp vars
define('CINTIENT_ASSETS_DIR', $GLOBALS['config']->valueForKey('base.work_dir') . 'assets/');
define('CINTIENT_AVATARS_DIR', CINTIENT_ASSETS_DIR . 'avatars/');
define('CINTIENT_BASE_URL', $GLOBALS['config']->valueForKey('base.url'));
define('CINTIENT_BUILDS_PAGE_LENGTH', 20);
define('CINTIENT_DATABASE_FILE', $GLOBALS['config']->valueForKey('base.database_file'));
define('CINTIENT_INSTALL_DIR', $GLOBALS['config']->valueForKey('base.install_dir'));
define('CINTIENT_LOG_FILE', $GLOBALS['config']->valueForKey('log.file'));
define('CINTIENT_NULL_BYTE_TOKEN', $GLOBALS['config']->valueForKey('base.null_byte_token'));
define('CINTIENT_PROJECT_CHECK_CHANGES_TIMEOUT_DEFAULT', 10); // Minutes
define('PASSWORD_SALT', $GLOBALS['config']->valueForKey('auth.password_salt'));
define('SCM_DEFAULT_CONNECTOR', 'svn'); // Taken from src/core/ScmConnector/

define('AUTH_METHOD', 'local'); // Taken from src/core/Auth/
define('CINTIENT_TEMP_UNIT_TESTS_DEFAULT_DIR', 'src/tests/');
define('CINTIENT_TEMP_UNIT_TESTS_DEFAULT_INCLUDE_MATCH', '*Test.php');
//define('CINTIENT_INSTALL_DIR', realpath(dirname(__FILE__) . '/../..') . '/');
//define('CINTIENT_WORK_DIR', '/www/.cintient/');
define('CINTIENT_JUNIT_REPORT_FILENAME', 'log-junit.xml');
define('CINTIENT_CODECOVERAGE_XML_REPORT_FILENAME', 'codecoverage.xml');
define('CINTIENT_CODECOVERAGE_HTML_DIR', 'codecoverage/');
//define('CINTIENT_BASE_URL', 'http://localhost/cintient-1.5-dev'); // Without trailing slash
define('CINTIENT_PROJECTS_DIR', $GLOBALS['config']->valueForKey('base.work_dir') . 'projects/');
define('CINTIENT_AVATAR_MAX_SIZE', 200 * 1024); // bytes
define('CINTIENT_AVATAR_WIDTH', 50);
define('CINTIENT_AVATAR_HEIGHT', 50);
define('CINTIENT_AVATAR_IMAGE_QUALITY', 90);
define('CINTIENT_INTERNAL_BUILDER_ACTIVE', 1);
define('DEFAULT_DIR_MASK', 0777);
define('CINTIENT_CONFIG_FILE' , $GLOBALS['config']->valueForKey('base.install_dir') . 'src/config/config.inc.php');
define('CINTIENT_DATABASE_SCHEMA_VERSION', '1111');
//
// The following is a workaround on the fact that the translation of this
// serialized object to the database gets all broken, due to the fact of PHP
// introducing NULL bytes around the '*' that is prepended before protected
// variable members, in the serialized mode. This method replaces those
// problematic NULL bytes with an identifier string '==',
// rendering serialization and unserialization of these specific kinds of
// object safe. Credits to travis@travishegner.com on:
// http://pt.php.net/manual/en/function.serialize.php#96504
//
define('CINTIENT_NEWLINE_TOKEN', '=n=');
define('CINTIENT_NEXT_TO_BUILD_PAGE_LENGTH', 2);
define('CHART_JUNIT_DEFAULT_WIDTH', 790);
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']->valueForKey('base.install_dir'));
define('CINTIENT_SQL_BUSY_RETRIES', 5);
// TODO: check host system and call the .bat version as appropriate.
define('CINTIENT_PHPDEPEND_BINARY', 'php ' . $GLOBALS['config']->valueForKey('base.install_dir') . 'lib/PHP_Depend/bin/pdepend.php');
define('CINTIENT_PHPDEPEND_JDEPEND_CHART_FILENAME', 'jdepend.svg');
define('CINTIENT_PHPDEPEND_OVERVIEW_PYRAMID_FILENAME', 'pyramid.svg');
define('CINTIENT_PHPDEPEND_SUMMARY_FILENAME', 'summary.xml');
define('CINTIENT_PHPCODESNIFFER_INCLUDE_FILE', $GLOBALS['config']->valueForKey('base.install_dir') . 'lib/PEAR/CodeSniffer.php');
define('CINTIENT_PHPCODESNIFFER_REPORT_XML_FILE', 'phpcsReportXml.xml');
define('CINTIENT_PHPCODESNIFFER_REPORT_FULL_FILE', 'phpcsReportFull.txt');

//
// Global stuff
//
// Get to the part of the URL that matters
$currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
$GLOBALS['uri'] = substr($currentUrl, strlen($GLOBALS['config']->valueForKey('base.url')));
if (substr($GLOBALS['uri'], -1) != '/') {
	$GLOBALS['uri'] .= '/';
}
SystemEvent::raise(SystemEvent::DEBUG, "Handling request. [URI={$GLOBALS['uri']}" . (empty($_SERVER['QUERY_STRING'])?'':'?'.html_entity_decode($_SERVER['QUERY_STRING'])) . "]", "index.php");
$GLOBALS['section'] = null;
$GLOBALS['settings'] = SystemSettings::load(); // Pull up system settings
$GLOBALS['smarty'] = null;
$GLOBALS['subSection'] = null;
$GLOBALS['templateFile'] = null;
$GLOBALS['templateMethod'] = null;
$GLOBALS['user'] = (isset($_SESSION['userId']) ? User::getById($_SESSION['userId']) : null);
$GLOBALS['project'] = ((!empty($_SESSION['projectId']) || !empty($_GET['pid'])) && !empty($GLOBALS['user']) ? Project::getById($GLOBALS['user'], (!empty($_GET['pid'])?$_GET['pid']:$_SESSION['projectId'])) : null);
$_SESSION['projectId'] = ($GLOBALS['project'] instanceof Project ? $GLOBALS['project']->getId() : null);



/*
/* +----------------------------------------------------------------+ *\
|* | POST-SETUP                                                     | *|
\* +----------------------------------------------------------------+ */
/*
if ($GLOBALS['settings'][SystemSettings::INTERNAL_BUILDER_ACTIVE]) {
	include 'src/handlers/buildHandler.php';
}
*/


/* +----------------------------------------------------------------+ *\
|* | URL HANDLING                                                   | *|
\* +----------------------------------------------------------------+ */

include '../src/HttpController.class.php';
$httpController = new HttpController();
$httpController->init();
$httpController->run();
$router = new Sentient\SimpleHttpRouter();
$router->setDelegate($httpController);
$router->init();
$router->run();


exit;
