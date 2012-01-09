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

/**
 * The PhpCodeSniffer special task handles Code Sniffer execution
 * for PHP projects and specifies interface logic.
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
class Build_BuilderElement_Task_Php_PhpCodeSniffer extends Build_BuilderElement
{
  protected $_fileExtensions; // Space separated list of allowed file extensions to sniff (applyable to dirs only)
  protected $_files;          // A space separated list of files and/or directories to check
  protected $_reportFullFile; // The file to which we should write the text report to (for direct showing the user)
  protected $_reportXmlFile;  // The file to which we should write the XML report to (for later processing)
  protected $_sniffs;         // A space separated list of sniffs to limit the check to,
                              // all must be part of the selected standard. This must be fed into
                              // PHP_CodeSniffer as a comma separated list.
  protected $_standard;       // The name of the available standard to check for

  public function __construct()
  {
    parent::__construct();
    $this->_fileExtensions = null;
    $this->_files = null;
    $this->_reportFullFile = null;
    $this->_reportXmlFile = null;
    $this->_sniffs = null;
    $this->_standard = null;
    $this->_specialTask = 'Build_SpecialTask_PhpCodeSniffer';
    // By default don't fail a build on code violations found.
    $this->_failOnError = false;
  }

  /**
   * Creates a new instance of this builder element.
   */
  static public function create()
  {
    $o = new self();
    $o->setFileExtensions('php');
    $o->setFiles('${sourcesDir}');
    $o->setReportFullFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_PHPCODESNIFFER_REPORT_FULL_FILE);
    $o->setReportXmlFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_PHPCODESNIFFER_REPORT_XML_FILE);
    $o->setStandard('Zend');
    return $o;
  }

  // TODO
  public function toAnt()
  {
    if (!$this->isActive()) {
      return true;
    }
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      array('cb' => 'getHtmlFailOnError'),
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'fileExtensions',
    		'label' => 'Allowed file extensions',
    		'value' => $this->getFileExtensions(),
    		'help' => 'No dots, and space separated.'
    	),
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'files',
    		'label' => 'Files or dirs to include',
    		'value' => $this->getFiles(),
    		'help' => 'Space separated.'
      ),
    	array(
    		'cb' => 'getHtmlInputText',
    		'name' => 'standard',
    		'value' => $this->getStandard()
      ),
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'sniffs',
        'label' => 'Limited to these sniffs',
        'value' => $this->getSniffs(),
    		'help' => 'Must be in chosen standard.'
      ),
    );
    parent::toHtml(array('title' => 'PhpCodeSniffer'), $callbacks);
  }

  // TODO
  public function toPhing()
  {
    if (!$this->isActive()) {
      return true;
    }
  }

  public function toPhp(Array &$context = array())
  {
    if (!$this->isActive()) {
      return '';
    }
    $php = '';
    if (!$this->getFiles()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files/dirs not set for task PHPCodeSniffer.', __METHOD__);
      return false;
    }
    if (!$this->getStandard()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No standard defined for task PHPCodeSniffer.', __METHOD__);
      return false;
    }

    $php .= "
\$GLOBALS['result']['task'] = 'phpcodesniffer';
// To avoid the 'PHP Notice:  Undefined index: argc' that happens when
// manually triggering a build
if (!isset(\$_SERVER['argc'])) {
  \$_SERVER['argc'] = 1;
}
\$getFailOnError = " . (int)$this->getFailOnError() . ";
require_once '" . CINTIENT_PHPCODESNIFFER_INCLUDE_FILE . "';
\$getFileExtensions = expandStr('{$this->getFileExtensions()}');
\$getFiles = expandStr('{$this->getFiles()}');
\$getSniffs = expandStr('{$this->getSniffs()}');
\$phpcs = new PHP_CodeSniffer_CLI();
\$values = \$phpcs->getDefaults();
\$values['extensions'] = (empty(\$getFileExtensions)?array():explode(' ', \$getFileExtensions));
\$values['files'] = (empty(\$getFiles)?array():explode(' ', \$getFiles));
\$values['reports']['xml'] = '{$this->getReportXmlFile()}';
\$values['reports']['full'] = '{$this->getReportFullFile()}';
\$values['reports']['source'] = null; // null means cout it, instead of file
\$values['sniffs'] = (empty(\$getSniffs)?array():explode(' ', \$getSniffs));
\$values['standard'] = expandStr('{$this->getStandard()}');
\$values['reportWidth'] = 110;
ob_start();
\$numErrors = \$phpcs->process(\$values);
output(ob_get_contents());
ob_end_clean();
// Try to clean up as much as we can
\$phpcs = null;
unset(\$phpcs);
if (\$numErrors === 0) {
  output(\"No code violations found. All code respects the '{\$values['standard']}' standard.\");
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
} else {
  output(\"Total of \$numErrors violation(s) found.\");
  if (\$getFailOnError) {
    \$GLOBALS['result']['ok'] = false;
	  return false;
  } else {
	  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
	}
}
";
    return $php;
  }
}