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
class Build_BuilderElement_Task_PhpUnit extends Build_BuilderElement
{
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
    $this->_codeCoverageXmlFile = null;
    $this->_codeCoverageHtmlDir = null;
    $this->_failOnFailure = true;
    $this->_failOnIncomplete = true;
    $this->_failOnSkipped = false;
    $this->_filesets = null;
    $this->_logJunitXmlFile = null;
  }

  public function toHtml()
  {
    parent::toHtml();
    if (!$this->isVisible()) {
      return true;
    }
    $o = $this;
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      $o->getHtmlTitle(array('title' => 'PhpUnit'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Fail on error?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnError',);
          if ($o->getFailOnError()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Fail on failure, checkbox
        h::div(array('class' => 'label'), 'Fail on failure?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnFailure',);
          if ($o->getFailOnFailure()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Fail on incomplete, checkbox
        h::div(array('class' => 'label'), 'Fail on incomplete?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnIncomplete',);
          if ($o->getFailOnIncomplete()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Fail on skipped, checkbox
        h::div(array('class' => 'label'), 'Fail on skipped?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnSkipped',);
          if ($o->getFailOnSkipped()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            $fileset->toHtml();
          }
        }
        // TODO: Add HTML button for adding new fileset.
      });
    });
  }

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
    $logJunitXmlFile = '';
    if ($this->getLogJunitXmlFile()) {
      $logJunitXmlFile = ' --log-junit ' . $this->getLogJunitXmlFile();
    }
    $codeCoverageXmlFile = '';
    if ($this->getCodeCoverageXmlFile()) {
      if (!extension_loaded('xdebug')) {
        $php .= "
output('Code coverage only possible with the Xdebug extension loaded. Option \"--coverage-clover\" disabled.');
";
      } else {
        $codeCoverageXmlFile = ' --coverage-clover ' . $this->getCodeCoverageXmlFile();
      }
    }
    $codeCoverageHtmlFile = '';
    if ($this->getCodeCoverageHtmlFile()) {
      if (!extension_loaded('xdebug')) {
        $php .= "
output('Code coverage only possible with the Xdebug extension loaded. Option \"--coverage-html\" disabled.');
";
      } else {
        $codeCoverageHtmlFile = ' --coverage-html ' . $this->getCodeCoverageHtmlFile();
      }
    }
    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $php .= "
" . $fileset->toPhp($context) . "
";
        //
        // In the following callback we assume that the fileset returns a
        // directory only *after* all it's content.
        //
        $php .= "
\$callback = function (\$entry) {
  \$ret = true;
  if (is_file(\$entry)) {
    \$thisutput = array();
    exec(\"" . CINTIENT_PHPUNIT_BINARY . "{$logJunitXmlFile}{$codeCoverageXmlFile}{$codeCoverageHtmlFile} \$entry\", \$thisutput, \$ret);
    foreach (\$thisutput as \$line) {
      output(\$line);
    }
    if (\$ret > 0) {
      \$ret = false;
    } else {
      \$ret = true;
    }
  }
  return \$ret;
};
if (!fileset{$fileset->getId()}_{$context['id']}(\$callback) && {$this->getFailOnError()}) {
  output('Tests failed.');
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