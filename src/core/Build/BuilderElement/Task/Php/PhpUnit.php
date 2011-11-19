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
 * The PhpUnit special task handles unit tests execution for PHP projects
 * and specifies interface logic.
 *
 * @package     Build
 * @subpackage  Task
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Task_Php_PhpUnit extends Build_BuilderElement
{
  protected $_bootstrapFile;
  protected $_codeCoverageXmlFile;
  protected $_codeCoverageHtmlDir;
  protected $_failOnFailure;       // PHPUnit distinguishes failures from errors
  protected $_failOnIncomplete;
  protected $_failOnSkipped;
  protected $_filesets;            // An array of fileset types
  protected $_logJunitXmlFile;

  public function __construct()
  {
    parent::__construct();
    $this->_bootstrapFile = null;
    $this->_codeCoverageXmlFile = null;
    $this->_codeCoverageHtmlDir = null;
    $this->_failOnFailure = true;
    $this->_failOnIncomplete = true;
    $this->_failOnSkipped = false;
    $this->_filesets = null;
    $this->_logJunitXmlFile = null;
    $this->_specialTask = 'Build_SpecialTask_PhpUnit';
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setType(Build_BuilderElement_Type_Fileset::FILE);
    $fileset->setDir('${sourcesDir}'/* . CINTIENT_TEMP_UNIT_TESTS_DEFAULT_DIR*/);
    $fileset->addInclude(CINTIENT_TEMP_UNIT_TESTS_DEFAULT_INCLUDE_MATCH);
    $o->setFilesets(array($fileset));
    $o->setLogJunitXmlFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_JUNIT_REPORT_FILENAME);
    $o->setCodeCoverageXmlFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_CODECOVERAGE_XML_REPORT_FILENAME);
    $o->setCodeCoverageHtmlDir($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_CODECOVERAGE_HTML_DIR);
    return $o;
  }

  // TODO
  public function toAnt() {}

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      array('cb' => 'getHtmlFailOnError'),
    	array(
    	  'cb' => 'getHtmlInputCheckbox',
    		'name' => 'failOnFailure',
    		'label' => 'Fail on failure?',
    		'value' => '',
    		'checked' => $this->getFailOnFailure(),
      ),
    	array(
    	  'cb' => 'getHtmlInputCheckbox',
    		'name' => 'failOnIncomplete',
    		'label' => 'Fail on incomplete?',
    		'value' => '',
    		'checked' => $this->getFailOnIncomplete(),
      ),
    	array(
    	  'cb' => 'getHtmlInputCheckbox',
    		'name' => 'failOnSkipped',
    		'label' => 'Fail on skipped?',
    		'value' => '',
    		'checked' => $this->getFailOnSkipped(),
      ),
      array(
      	'cb' => 'getHtmlInputText',
      	'name' => 'bootstrapFile',
    		'label' => 'Bootstrap file',
      	'value' => $this->getBootstrapFile()
      ),
    	array('cb' => 'getFilesets'),
    );
    parent::toHtml(array('title' => 'PhpUnit'), $callbacks);
  }

  // TODO
  public function toPhing() {}

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files not set for task PHPUnit.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'phpunit';
output('Starting unit tests...');
";
    // PHPUnit's internals always preemptively array_shift(), supposedly
    // the executable script's complete filename
    $php .= "
\$args = array('dummyfirstentry');";
    // Omit --no-globals-backup to see all kinds of weird shit happen,
    // like for instance a project's OK status not being saved after a
    // successful build. Don't know if this will ever be required by
    // anyone doing unit tests, but really hope not. With the current
    // Cintient implementation, we'd be in trouble if backup was on.
    $php .= "
\$args[] = '--no-globals-backup';
";
    if ($this->getLogJunitXmlFile()) {
      $php .= "
\$args[] = '--log-junit';
\$args[] = '{$this->getLogJunitXmlFile()}';
";
    }
    if ($this->getCodeCoverageXmlFile()) {
      $php .= "
if (!extension_loaded('xdebug')) {
  output('Code coverage only possible with the Xdebug extension loaded. Option \"--coverage-clover\" disabled.');
} else {
	\$args[] = '--coverage-clover';
	\$args[] = '{$this->getCodeCoverageXmlFile()}';
}
";
    }

    if ($this->getCodeCoverageHtmlDir()) {
      $php .= "
if (!extension_loaded('xdebug')) {
	output('Code coverage only possible with the Xdebug extension loaded. Option \"--coverage-html\" disabled.');
} else {
	\$args[] = '--coverage-html';
	\$args[] = '{$this->getCodeCoverageHtmlDir()}';
}
";
    }

    if ($this->getBootstrapFile()) {
      $php .= "
\$args[] = '--bootstrap';
\$args[] = '{$this->getBootstrapFile()}';
";
    }

    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $php .= "
" . $fileset->toPhp($context) . "
require_once 'PHPUnit/Autoload.php';
define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
\$callback = function (\$entry) use (\$args) {
  \$ret = true;
  if (is_file(\$entry)) {
    \$_SERVER['argv'] = \$args; // This resets \$_SERVER['argv'] so that \$entry comes up next
    \$_SERVER['argv'][] = \"\$entry\"; // \$entry is a SlpFileInfo, force it __toString()
    \$_SERVER['argc'] = count(\$_SERVER['argv']); // For consistency sake
    ob_start();
    \$ret = PHPUnit_TextUI_Command::main(false);
    output(ob_get_contents());
    ob_end_clean();
    if (\$ret > 0) {
      \$ret = false;
    } else {
      \$ret = true;
    }
    unset(\$_SERVER['argv']);
    unset(\$_SERVER['argc']);
  }
  return \$ret;
};
if (!fileset{$fileset->getId()}_{$context['id']}(\$callback) && {$this->getFailOnError()}) {
  output('Unit testing failed.');
  \$GLOBALS['result']['ok'] = false;
  return false;
} else {
	\$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  output('All tests ok.');
}
";
      }
    }
    return $php;
  }
}