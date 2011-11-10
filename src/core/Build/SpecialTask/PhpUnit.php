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
 * PhpUnit deals with unit tests tasks and chart generation from the
 * Junit XML report results.
 *
 * @package     Build
 * @subpackage  SpecialTask
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_SpecialTask_PhpUnit extends Framework_DatabaseObjectAbstract implements Build_SpecialTaskInterface
{
  protected $_ptrProjectBuild; // Redundant but necessary for save()
  protected $_buildId;         // The project build ID serves as this instance's ID
  protected $_date;            // should practically coincide with the build's date
  protected $_version;

  public function __construct(Project_Build $build)
  {
    parent::__construct();
    $this->_ptrProjectBuild = $build;
    $this->_buildId = $build->getId();
    $this->_date = null;
    $this->_version = '';
  }

  public function __destruct()
  {
    parent::__destruct();
  }

  public function preBuild()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    return true;
  }

  public function postBuild()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Called.", __METHOD__);
    // Backup the original junit report file
    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_JUNIT_REPORT_FILENAME, $this->getPtrProjectBuild()->getBuildDir() . CINTIENT_JUNIT_REPORT_FILENAME)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original Junit XML file [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
      return false;
    }
    // Backup the original codecoverage xml report file
    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_CODECOVERAGE_XML_REPORT_FILENAME, $this->getPtrProjectBuild()->getBuildDir() . CINTIENT_CODECOVERAGE_XML_REPORT_FILENAME)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original code coverage XML file [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
      return false;
    }
    // Copy the original codecoverage html dir and "massage" the files
    $ret = true;
    $origCovDir = $this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_CODECOVERAGE_HTML_DIR;
    if (file_exists($origCovDir)) {
      $htmlCovDir = $this->getPtrProjectBuild()->getBuildDir() . CINTIENT_CODECOVERAGE_HTML_DIR;
      if (!@mkdir($htmlCovDir) || !file_exists($htmlCovDir)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not create dir for HTML coverage files [DIR={$htmlCovDir}] [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
        return false;
      }
      foreach (new DirectoryIterator($origCovDir) as $file) {
        if ($file->isDot()) {
          continue;
        }
        $src = $origCovDir . $file;
        $dest = $htmlCovDir . $file;
        if (!copy($src, $dest)) {
          SystemEvent::raise(SystemEvent::ERROR, "Problems copying a file from the original codecoverage HTML dir [FILE={$file}] [DIR={$htmlCovDir}] [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
          $ret = false;
        } else {
          $ret = $ret & true;
          //
          // We're taking the chance to hack into every code coverage
          // HTML generated file and bent it to our purposes. While we
          // don't have the ability to generate our very own
          //
          $destHtml = file_get_contents($dest);
          $that = $this; # Wow, beautiful, thank you PHP 5.3.x...
          //
          // Get all links working apropriately for Cintient
          //
          $newDestHtml = preg_replace_callback(
            '/((?:src|href)=)"([\w.#]+)"/m',
            function ($match) use ($that)
            {
              return $match[1] . '"' . UrlManager::getForAsset($match[2], array('bid' => $that->getBuildId(), 'cc' => true)) . '"';
            },
            $destHtml,
            -1
          );
          // Add a few CSS tweaks
          SystemEvent::raise(SystemEvent::ERROR, $file, __METHOD__);
          if ($file == 'style.css') {
            $newDestHtml .= "html,body,table,tr,td,a{font-size:8pt;font-family:sans-serif,arial,helvetica;}";
            $newDestHtml .= <<<EOT
body {
  background: #FBFBFB; /* non-CSS3 */
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#eeeeee'); /* for IE */
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0.16, #fff), color-stop(0.9, #eee)); /* for webkit browsers */
  background: -moz-linear-gradient(top, #fff 16%, #eee 90%); /* for firefox 3.6+ */
}
EOT;
          }
          file_put_contents($dest, $newDestHtml);
        }
      }
      // TODO: remove the original codecoverage dir.
    }
    return $ret;
  }

  public function getViewData(Array $params = array())
  {
    $junitReportFile = $this->getPtrProjectBuild()->getBuildDir() . CINTIENT_JUNIT_REPORT_FILENAME;
    if (!is_file($junitReportFile)) {
      SystemEvent::raise(SystemEvent::ERROR, "Junit file not found. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}] [FILE={$junitReportFile}]", __METHOD__);
      return false;
    }
    try {
      $xml = new SimpleXMLElement($junitReportFile, 0, true);
    } catch (Exception $e) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems processing Junit XML file. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
      return false;
    }
    // Apparently using call_user_func(__FUNCTION__) inside a closure,
    // doesn't work... Anyway I'm just going for a closure here, to
    // avoid the whole function definition crap here, inside a method.
    //
    // This closure takes a SimpleXMLElement loaded with the Junit report
    // and searches for the first element with a file attribute. That
    // level is the Class Test level, and what we want here is to iterate
    // over all class tests.
    $f = function ($node) use (&$parent, &$f)
    {
      if (isset($node->attributes()->file)) {
        return $parent->children();
      } else {
        $parent = $node;
        return $f($node->children());
      }
    };
    $parent = $xml;
    $classTestsXml = $f($xml->children());
    $classes = array();
    foreach ($classTestsXml as $node) {
      $imageFilename = '';
      $methodsNames = array();
      $methods = array();
      $class = new TestClass();
      $class->setName((string)$node->attributes()->name);
      $class->setFile((string)$node->attributes()->file);
      $class->setTests((string)$node->attributes()->tests);
      $class->setAssertions((string)$node->attributes()->assertions);
      $class->setFailures((string)$node->attributes()->failures);
      $class->setErrors((string)$node->attributes()->errors);
      $class->setTime((string)$node->attributes()->time);
      $class->setChartFilename(md5($this->getProjectId() . $this->getProjectBuildId() . $class->getFile()) . '.png');
      // Right here we're exactly at the test class (file) root level,
      // with level 1 being the unit test (method of the original class)
      // and level 2 being the various datasets used in the test (each a
      // test case).
      foreach ($node->children() as $methodXml) {
        $time = (float)$methodXml->attributes()->time * 1000; // to milliseconds
        $methodsNames[] = (string)$methodXml->attributes()->name;
        $f = ((((float)$methodXml->attributes()->failures) * $time) / (float)$methodXml->attributes()->assertions);

        $method = new TestMethod();
        $method->setName((string)$methodXml->attributes()->name);
        $method->setTests((string)$methodXml->attributes()->tests);
        $method->setAssertions((string)$methodXml->attributes()->assertions);
        $method->setFailures((string)$methodXml->attributes()->failures);
        $method->setErrors((string)$methodXml->attributes()->errors);
        $method->setTime((string)$methodXml->attributes()->time);
        $method->setCalculatedOks(((float)$time - (float)$f));
        $method->setCalculatedFaileds($f);

        $methods[] = $method;
      }
      $class->setTestMethods($methods);
      $classes[] = $class;
    }

    $ret = array();
    $ret['project_buildJunit'] = $classes;

    return $ret;
  }

  /**
   * A slightly different version of the base _getCurrentSignature() is
   * needed, i.e., pointer to Project_Build is not to be considered.
   */
  protected function _getCurrentSignature(array $exclusions = array())
  {
    return parent::_getCurrentSignature(array('_ptrProjectBuild'));
  }

  /**
   * Getter for the project build ID
   */
  public function getProjectBuildId()
  {
    return $this->_ptrProjectBuild->getId();
  }

	/**
   * Getter for the project ID
   */
  public function getProjectId()
  {
    return $this->_ptrProjectBuild->getPtrProject()->getId();
  }

  public function init()
  {
    return true;
  }

  protected function _save($force = false)
  {
    return true;
  }

  static private function _getObject(Resultset $rs, Project_Build $build)
  {
    $ret = new self($build);
    $ret->setDate($rs->getDate());
    $ret->setVersion($rs->getVersion());
    $ret->resetSignature();
    return $ret;
  }

  static public function install(Project $project)
  {
    return true;
  }

  static public function uninstall(Project $project)
  {
    return true;
  }

  static public function getById(Project_Build $build, User $user, $access = Access::READ, array $options = array())
  {
    return new self($build);
  }
}
