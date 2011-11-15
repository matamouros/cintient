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
 * PhpDepend is a helper class for dealing with PHP_Depend third party
 * library. It specifically integrates with Project_Build, abstracts all
 * interactions with PHP_Depend and maintains a record of all high-level
 * collected metrics.
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
class Build_SpecialTask_PhpDepend extends Framework_DatabaseObjectAbstract implements Build_SpecialTaskInterface
{
  protected $_ptrProjectBuild; // Redundant but necessary for save()
  protected $_buildId;         // The project build ID serves as this instance's ID
  protected $_date;            // should practically coincide with the build's date
  protected $_version;
  protected $_ahh;
  protected $_andc;
  protected $_calls;
  protected $_ccn;
  protected $_ccn2;
  protected $_cloc;
  protected $_clsa;
  protected $_clsc;
  protected $_eloc;
  protected $_fanout;
  protected $_leafs;
  protected $_lloc;
  protected $_loc;
  protected $_maxDit;
  protected $_ncloc;
  protected $_noc;
  protected $_nof;
  protected $_noi;
  protected $_nom;
  protected $_nop;
  protected $_roots;

  public function __construct(Project_Build $build)
  {
    parent::__construct();
    $this->_ptrProjectBuild = $build;
    $this->_buildId = $build->getId();
    $this->_date = null;
    $this->_version = '';
    $this->_ahh = '';
    $this->_andc = '';
    $this->_calls = '';
    $this->_ccn = '';
    $this->_ccn2 = '';
    $this->_cloc = '';
    $this->_clsa = '';
    $this->_clsc = '';
    $this->_eloc = '';
    $this->_fanout = '';
    $this->_leafs = '';
    $this->_lloc = '';
    $this->_loc = '';
    $this->_maxDit = '';
    $this->_ncloc = '';
    $this->_noc = '';
    $this->_nof = '';
    $this->_noi = '';
    $this->_nom = '';
    $this->_nop = '';
    $this->_roots = '';
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

    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_PHPDEPEND_JDEPEND_CHART_FILENAME, $this->getPtrProjectBuild()->getBuildDir() . $this->getJdependChartFilename())) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original PHP_Depend jdepend chart file [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
    }
    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_PHPDEPEND_OVERVIEW_PYRAMID_FILENAME, $this->getPtrProjectBuild()->getBuildDir() . $this->getOverviewPyramidFilename())) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original PHP_Depend overview pyramid file [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
    }
    if (!@copy($this->getPtrProjectBuild()->getPtrProject()->getReportsWorkingDir() . CINTIENT_PHPDEPEND_SUMMARY_FILENAME, $this->getPtrProjectBuild()->getBuildDir() . $this->getPhpDependSummaryFilename())) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not backup original PHP_Depend summary file [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
    }
    //
    // TODO: Process the PHP_Depend summary file and maintain an history record
    //
    $overviewPyramidFile = $this->getPtrProjectBuild()->getBuildDir() . $this->getPhpDependSummaryFilename();
    if (!is_file($overviewPyramidFile)) {
      SystemEvent::raise(SystemEvent::ERROR, "Summary file not found. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}] [FILE={$overviewPyramidFile}]", __METHOD__);
      return false;
    }
    try {
      $xml = new SimpleXMLElement($overviewPyramidFile, 0, true);
    } catch (Exception $e) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems processing summary XML file. [PID={$this->getProjectId()}] [BUILD={$this->getProjectBuildId()}]", __METHOD__);
      return false;
    }
    $this->setAhh($xml->attributes()->ahh->__toString());
    $this->setAndc($xml->attributes()->andc->__toString());
    $this->setCalls($xml->attributes()->calls->__toString());
    $this->setCcn($xml->attributes()->ccn->__toString());
    $this->setCcn2($xml->attributes()->ccn2->__toString());
    $this->setCloc($xml->attributes()->cloc->__toString());
    $this->setClsa($xml->attributes()->clsa->__toString());
    $this->setClsc($xml->attributes()->clsc->__toString());
    $this->setEloc($xml->attributes()->eloc->__toString());
    $this->setFanout($xml->attributes()->fanout->__toString());
    $this->setLeafs($xml->attributes()->leafs->__toString());
    $this->setLloc($xml->attributes()->lloc->__toString());
    $this->setLoc($xml->attributes()->loc->__toString());
    $this->setMaxDit($xml->attributes()->maxDIT->__toString());
    $this->setNcloc($xml->attributes()->ncloc->__toString());
    $this->setNoc($xml->attributes()->noc->__toString());
    $this->setNof($xml->attributes()->nof->__toString());
    $this->setNoi($xml->attributes()->noi->__toString());
    $this->setNom($xml->attributes()->nom->__toString());
    $this->setNop($xml->attributes()->nop->__toString());
    $this->setRoots($xml->attributes()->roots->__toString());

    return true;
  }

  public function getViewData(Array $params = array())
  {
    $ret = array();
    if ($this->getJdependChartFilename() !== false && file_exists($this->_ptrProjectBuild->getBuildDir() . $this->getJdependChartFilename())) {
      $ret['project_jdependChartFilename'] = $this->getJdependChartFilename();
    }
    if ($this->getOverviewPyramidFilename() !== false && file_exists($this->_ptrProjectBuild->getBuildDir() . $this->getOverviewPyramidFilename())) {
      $ret['project_overviewPyramidFilename'] = $this->getOverviewPyramidFilename();
    }
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


  /**
   *
   * Contrary to the unit tests report, the code quality reports are
   * already generated by the actual PHP_Depend command. We will just
   * use this to keep a history record of quality metrics throughout
   * all builds.
   *
   */
  public function createReportFromPhpDepend()
  {

  }


  public function getJdependChartFilename()
  {
    return md5($this->getProjectId() . $this->getProjectBuildId() . CINTIENT_PHPDEPEND_JDEPEND_CHART_FILENAME) . '.svg';
  }


  public function getPhpDependSummaryFilename()
  {
    return md5($this->getProjectId() . $this->getProjectBuildId() . CINTIENT_PHPDEPEND_SUMMARY_FILENAME) . '.xml';
  }


  public function getOverviewPyramidFilename()
  {
    return md5($this->getProjectId() . $this->getProjectBuildId() . CINTIENT_PHPDEPEND_OVERVIEW_PYRAMID_FILENAME) . '.svg';
  }


  public function init()
  {
    return true;
  }


  protected function _save($force = false)
  {
    if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
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
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
      return false;
    }

    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved.", __METHOD__);
    #endif

    $this->resetSignature();
    return true;
  }


  static private function _getObject(Resultset $rs, Project_Build $build)
  {
    $ret = new self($build);

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
    return $ret;
  }


  static public function install(Project $project)
  {
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
    }
    return true;
  }


  static public function uninstall(Project $project)
  {
    $sql = "DROP TABLE phpdepend{$project->getId()}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete table. [TABLE={$project->getId()}]", __METHOD__);
      return false;
    }
    return true;
  }


  static public function getById(Project_Build $build, User $user, $access = Access::READ, array $options = array())
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pd.*'
         . ' FROM phpdepend' . $build->getProjectId() . ' pd, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' AND pd.buildid=?';
    $val = array($build->getProjectId(), $user->getId(), $access, $build->getId());
    if ($rs = Database::query($sql, $val)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $build);
      }
    }
    return $ret;
  }
}
