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
 * Helper class for dealing with PHP_CodeSniffer third party library,
 * integrating directly with Project_Build. It also keeps collected
 * metrics, for later analysis.
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
class Build_SpecialTask_PhpCodeSniffer extends Framework_DatabaseObjectAbstract implements Build_SpecialTaskInterface
{
  protected $_ptrProjectBuild; // Redundant but necessary for save()
  protected $_buildId;         // The project build ID serves as this instance's ID
  protected $_date;            // Should practically coincide with the build's date
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

    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_PHPCODESNIFFER_REPORT_XML_FILE, $this->getPtrProjectBuild()->getBuildDir() . $this->getReportXmlFilename())) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original XML report file. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
    }
    $reportFullFile = $this->getPtrProjectBuild()->getBuildDir() . $this->getReportFullFilename();
    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_PHPCODESNIFFER_REPORT_FULL_FILE, $reportFullFile)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original Full report file. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
    }
    // Clean up the direct to user report file, so we don't have to do it
    // each time on getViewData()
    $fd = fopen($reportFullFile, 'r');
    $originalFile = fread($fd, filesize($reportFullFile));
    fclose($fd);
    // Pretty dummy replacement of the path before the sources dir, trying
    // to hide as much as possible from the user (readibility purposes)
    $treatedFile = str_replace($this->getPtrProjectBuild()->getPtrProject()->getScmLocalWorkingCopy(), '', $originalFile);
    $treatedFile = Framework_SmartyPlugin::raw2html($treatedFile);
    file_put_contents($reportFullFile, $treatedFile);

    //
    // TODO: Process the XML report file, store for history purposes
    // TODO2: Also create an HTML file, with results, for direct inclusion by Smarty?
    //

    return true;
  }

  public function getReportFullFilename()
  {
    return md5($this->getProjectId() . $this->getProjectBuildId() . CINTIENT_PHPCODESNIFFER_REPORT_FULL_FILE) . '.txt';
  }

  public function getReportXmlFilename()
  {
    return md5($this->getProjectId() . $this->getProjectBuildId() . CINTIENT_PHPCODESNIFFER_REPORT_XML_FILE) . '.xml';
  }

  public function getViewData()
  {
    $ret = array();
    $reportFullFile = $this->getPtrProjectBuild()->getBuildDir() . $this->getReportFullFilename();
    if ($this->getReportFullFilename() && file_exists($reportFullFile)) {
      // [relevantly] faster than file_get_contents?
      $fd = fopen($reportFullFile, 'r');
      $ret['project_phpcsFullReport'] = nl2br(str_replace(' ', '&nbsp;', fread($fd, filesize($reportFullFile))));
      fclose($fd);
    }
    return $ret;
  }

  /**
   * A slightly different version of the base _getCurrentSignature() is
   * needed, i.e., pointer to Project_Build is not to be considered.
   */
  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    $arr['_ptrProjectBuild'] = null;
    unset($arr['_ptrProjectBuild']);
    return md5(serialize($arr));
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
  {}

  protected function _save($force = false)
  {
    if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
    }
/*
    if (!Database::beginTransaction()) {
      return false;
    }
    $sql = 'REPLACE INTO phpdepend' . $this->getProjectId()
         . ' (buildid, date, version, ahh, andc, calls, ccn, ccn2, cloc,'
         . ' clsa, clsc, eloc, fanout, leafs, lloc, loc, maxdit, ncloc,'
         . ' noc, nof, noi, nom, nop, roots)'
         . ' VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $val = array(
      $this->getBuildId(),
      $this->getDate(),
      $this->getVersion(),
      $this->getAhh(),
      $this->getAndc(),
      $this->getCalls(),
      $this->getCcn(),
      $this->getCcn2(),
      $this->getCloc(),
      $this->getClsa(),
      $this->getClsc(),
      $this->getEloc(),
      $this->getFanout(),
      $this->getLeafs(),
      $this->getLloc(),
      $this->getLoc(),
      $this->getMaxDit(),
      $this->getNcloc(),
      $this->getNoc(),
      $this->getNof(),
      $this->getNoi(),
      $this->getNom(),
      $this->getNop(),
      $this->getRoots(),
    );

    if (!Database::execute($sql, $val)) {
      Database::rollbackTransaction();
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
      return false;
    }

    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The object might not have been saved.", __METHOD__);
      return false;
    }

    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved.", __METHOD__);
    #endif

    $this->resetSignature();*/
    return true;
  }

  static private function _getObject(Resultset $rs, Project_Build $build)
  {
    /*
    $ret = new PhpDepend($build);

    $ret->setDate($rs->getDate());
    $ret->setVersion($rs->getVersion());
    $ret->setAhh($rs->getAhh());
    $ret->setAndc($rs->getAndc());
    $ret->setCalls($rs->getCalls());
    $ret->setCcn($rs->getCcn());
    $ret->setCcn2($rs->getCcn2());
    $ret->setCloc($rs->getCloc());
    $ret->setClsa($rs->getClsa());
    $ret->setClsc($rs->getClsc());
    $ret->setEloc($rs->getEloc());
    $ret->setFanout($rs->getFanout());
    $ret->setLeafs($rs->getLeafs());
    $ret->setLloc($rs->getLloc());
    $ret->setLoc($rs->getLoc());
    $ret->setMaxDit($rs->getMaxDit());
    $ret->setNcloc($rs->getNcloc());
    $ret->setNoc($rs->getNoc());
    $ret->setNof($rs->getNof());
    $ret->setNoi($rs->getNoi());
    $ret->setNom($rs->getNom());
    $ret->setNop($rs->getNop());
    $ret->setRoots($rs->getRoots());

    $ret->resetSignature();
    return $ret;*/
    return new self($build);
  }

  static public function install(Project $project)
  {
    /*
    $sql = "
CREATE TABLE IF NOT EXISTS phpdepend{$project->getId()} (
  buildid INTEGER PRIMARY KEY,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  version TEXT NOT NULL DEFAULT '" . CINTIENT_DATABASE_SCHEMA_VERSION . "',
  ahh REAL UNSIGNED NOT NULL DEFAULT 0.0,
  andc REAL UNSIGNED NOT NULL DEFAULT 0.0,
  calls INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ccn INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ccn2 INTEGER UNSIGNED NOT NULL DEFAULT 0,
  cloc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  clsa INTEGER UNSIGNED NOT NULL DEFAULT 0,
  clsc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  eloc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  fanout INTEGER UNSIGNED NOT NULL DEFAULT 0,
  leafs INTEGER UNSIGNED NOT NULL DEFAULT 0,
  lloc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  loc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  maxdit INTEGER UNSIGNED NOT NULL DEFAULT 0,
  ncloc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  noc INTEGER UNSIGNED NOT NULL DEFAULT 0,
  nof INTEGER UNSIGNED NOT NULL DEFAULT 0,
  noi INTEGER UNSIGNED NOT NULL DEFAULT 0,
  nom INTEGER UNSIGNED NOT NULL DEFAULT 0,
  nop INTEGER UNSIGNED NOT NULL DEFAULT 0,
  roots INTEGER UNSIGNED NOT NULL DEFAULT 0
);
";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems creating table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }*/
    return true;
  }

  static public function uninstall(Project $project)
  {
    /*
    $sql = "DROP TABLE phpcodesniffer{$project->getId()}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }*/
    return true;
  }

  static public function getById(Project_Build $build, User $user, $access = Access::READ, array $options = array())
  {
    return new self($build);
  }
}
