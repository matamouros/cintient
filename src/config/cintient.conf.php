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

error_reporting(-1);

/**
 * Log severity is in accordance with the consts defined in SystemEvent
 */
define('CINTIENT_LOG_SEVERITY_DEBUG',     2048);
define('CINTIENT_LOG_SEVERITY_INFO',      1024);
define('CINTIENT_LOG_SEVERITY_NOTICE',    512);
define('CINTIENT_LOG_SEVERITY_WARNING',   256);
define('CINTIENT_LOG_SEVERITY_ERROR',     128);
define('CINTIENT_LOG_SEVERITY_CRITICAL',  64);
define('CINTIENT_LOG_SEVERITY_ALERT',     32);
define('CINTIENT_LOG_SEVERITY_EMERGENCY', 16);

define('CINTIENT_LOG_SEVERITY', CINTIENT_LOG_SEVERITY_DEBUG);

define('CINTIENT_TEMP_UNIT_TESTS_DEFAULT_DIR', 'src/tests/');
define('CINTIENT_TEMP_UNIT_TESTS_DEFAULT_INCLUDE_MATCH', '*Test.php');


define('CINTIENT_WORK_DIR', ''); // TODO: This should be pulled from the user .ini config file
define('LOG_FILE', CINTIENT_WORK_DIR . 'cintient.log');
define('CINTIENT_INSTALL_DIR', '');
define('CINTIENT_JUNIT_REPORT_FILENAME', 'log-junit.xml');
define('CINTIENT_CODECOVERAGE_XML_REPORT_FILENAME', 'codecoverage.xml');
define('CINTIENT_CODECOVERAGE_HTML_DIR', 'codecoverage/');

define('CINTIENT_BASE_URL', ''); // Without trailing slash

define('CINTIENT_PROJECTS_DIR', CINTIENT_WORK_DIR . 'projects/');

define('CINTIENT_ASSETS_DIR', CINTIENT_WORK_DIR . 'assets/');
define('CINTIENT_AVATARS_DIR', CINTIENT_ASSETS_DIR . 'avatars/');

define('CINTIENT_AVATAR_MAX_SIZE', 50 * 1024); // bytes

define('CINTIENT_AVATAR_WIDTH', 50);
define('CINTIENT_AVATAR_HEIGHT', 50);

define('CINTIENT_PROJECT_CHECK_CHANGES_TIMEOUT_DEFAULT', 10); // Minutes

define('CINTIENT_AVATAR_IMAGE_QUALITY', 90);

define('CINTIENT_INTERNAL_BUILDER_ACTIVE', 1);

define('DEFAULT_DIR_MASK', 0777);

define('CINTIENT_CONFIG_FILE' , CINTIENT_INSTALL_DIR . 'src/config/config.inc.php');

define('AUTH_METHOD', 'local'); // Taken from src/core/Auth/

define('SCM_DEFAULT_CONNECTOR', 'svn'); // Taken from src/core/ScmConnector/

define('SMARTY_DEBUG', false);
define('SMARTY_FORCE_COMPILE', false);
define('SMARTY_COMPILE_CHECK', true);
define('SMARTY_TEMPLATE_DIR', CINTIENT_INSTALL_DIR . 'src/templates/');
define('SMARTY_COMPILE_DIR', '/tmp/');

define('PASSWORD_SALT', 'rOTA4spNYI3yXvAL');

define('CINTIENT_DATABASE_FILE', CINTIENT_WORK_DIR . 'cintient.sqlite');

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
define('CINTIENT_NULL_BYTE_TOKEN', '=0=');

define('CINTIENT_NEWLINE_TOKEN', '=n=');

define('CINTIENT_PHP_BINARY', 'php');

define('CINTIENT_BUILDS_PAGE_LENGTH', 20);

define('CHART_JUNIT_DEFAULT_WIDTH', 790);

// Set the include path
set_include_path(get_include_path() . PATH_SEPARATOR . CINTIENT_INSTALL_DIR);


//
// PHP_Depend
//
// TODO: check host system and call the .bat version as appropriate.
define('CINTIENT_PHPDEPEND_BINARY', CINTIENT_PHP_BINARY . ' ' . CINTIENT_INSTALL_DIR . 'lib/PHP_Depend/bin/pdepend.php');
define('CINTIENT_PHPDEPEND_JDEPEND_CHART_FILENAME', 'jdepend.svg');
define('CINTIENT_PHPDEPEND_OVERVIEW_PYRAMID_FILENAME', 'pyramid.svg');
define('CINTIENT_PHPDEPEND_SUMMARY_FILENAME', 'summary.xml');

//
// PHP_CodeSniffer
//
define('CINTIENT_PHPCODESNIFFER_INCLUDE_FILE', CINTIENT_INSTALL_DIR . 'lib/PEAR/CodeSniffer.php');
define('CINTIENT_PHPCODESNIFFER_REPORT_XML_FILE', 'phpcsReportXml.xml');
define('CINTIENT_PHPCODESNIFFER_REPORT_FULL_FILE', 'phpcsReportFull.txt');



// Register our autoloader
function autoloadCintient($classname)
{
  if (strpos($classname, '_') !== false) {
    $classname = str_replace('_', '/', $classname);
  }
  if (is_file(CINTIENT_INSTALL_DIR . 'src/core/' . $classname . '.php')) {
    include 'src/core/' . $classname . '.php';
  }
}
spl_autoload_register('autoloadCintient');
