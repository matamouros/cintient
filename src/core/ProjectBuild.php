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
 * @package Project
 */
class ProjectBuild
{
  private $_id;           // the build's incremental ID
  private $_date;         // the build's date
  private $_label;        // the label on the build, also used to name the release package file
  private $_description;  // a user generated description text (prior or after the build triggered).
  private $_output;       // the integration builder's output collected
  private $_status;       // indicates: failure | no_release | release
  private $_project;      // the project ID goes into the table name - it's not an attribute
  private $_signature;    // Internal flag to control whether a save to database is required
  private $_scmRevision;  // The corresponding SCM revision on the remote repository

  const STATUS_FAIL = 0;
  const STATUS_OK_WITHOUT_PACKAGE = 1;
  const STATUS_OK_WITH_PACKAGE = 2;

  /**
   * Magic method implementation for calling vanilla getters and setters. This
   * is rigged to work only with private/protected non-static class variables
   * whose nomenclature follows the Zend Coding Standard.
   *
   * @param $name
   * @param $args
   */
  public function __call($name, $args)
  {
    if (strpos($name, 'get') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      return $this->$var;
    } elseif (strpos($name, 'set') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      $this->$var = $args[0];
      return true;
    }
    return false;
  }

  public function __construct(Project $project)
  {
    $this->_project = $project;
    $this->_id = null;
    $this->_date = null;
    $this->_label = '';
    $this->_description = '';
    $this->_output = '';
    $this->_status = self::STATUS_FAIL;
    $this->_signature = null;
    $this->_scmRevision = null;
  }

  public function __destruct()
  {
    $this->_save();
  }

  public function createReportFromJunit()
  {
    $junitReportFile = $this->getReportsDir() . CINTIENT_JUNIT_REPORT_FILENAME;
    if (!is_file($junitReportFile)) {
      SystemEvent::raise(SystemEvent::ERROR, "Junit file not found. [PID={$this->getProject()->getId()}] [BUILD={$this->getId()}] [FILE={$junitReportFile}]", __METHOD__);
      return false;
    }
    //
    // Access file testsuites directly (last level before testcases).
    // This can't be a closure because of its recursiveness.
    //
    function f($node) {
      if (isset($node->attributes()->file)) {
        return $node;
      } else {
        return f($node->children());
      }
    }
    try {
      $xml = new SimpleXMLElement($junitReportFile, 0, true);
    } catch (Exception $e) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems processing Junit XML file. [PID={$this->getProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
      return false;
    }
    $xmls = $xml->children();
    foreach ($xmls as $node) {
      $imageFilename = '';
      $successes = array(); // assertions - failures
      $failures = array();
      $methodsNames = array();
      $classes = array();
      $methods = array();
      $classXml = f($node);
      $class = new TestClass();
      $class->setName($classXml->attributes()->name);
      $class->setFile((string)$classXml->attributes()->file);
      $class->setTests((string)$classXml->attributes()->tests);
      $class->setAssertions((string)$classXml->attributes()->assertions);
      $class->setFailures((string)$classXml->attributes()->failures);
      $class->setErrors((string)$classXml->attributes()->errors);
      $class->setTime((string)$classXml->attributes()->time);
      $class->setChartFilename(md5($this->getProject()->getId() . $this->getId() . $class->getFile()) . '.png');
      //
      // After f() we're exactly at the test class (file) root level,
      // with level 1 being the unit test (method of the original class)
      // and level 2 being the various datasets used in the test (each a
      // test case).
      //
      foreach ($classXml->children() as $methodXml) {
        $method = new TestMethod();
        $method->setName($methodXml->getName());
        $method->setTests((string)$methodXml->attributes()->tests);
        $method->setAssertions((string)$methodXml->attributes()->assertions);
        $method->setFailures((string)$methodXml->attributes()->failures);
        $method->setErrors((string)$methodXml->attributes()->errors);
        $method->setTime((string)$methodXml->attributes()->time);
        $methods[] = $method;

        $time = (float)$methodXml->attributes()->time * 1000; // to milliseconds
        $methodsNames[] = $methodXml->attributes()->name;
        $f = ((((float)$methodXml->attributes()->failures) * $time) / (float)$methodXml->attributes()->assertions);
        $successes[] = (float)$time - (float)$f;
        $failures[] = $f;
      }

      $chartWidth = CHART_JUNIT_DEFAULT_WIDTH;
      $chartHeight = 25 * count($methodsNames) + 60;

      /* pChart library inclusions */
      include 'lib/pChart/class/pData.class';
      include 'lib/pChart/class/pDraw.class';
      include 'lib/pChart/class/pImage.class';

      $MyData = new pData();
      $MyData->addPoints($successes, 'Ok');
      $MyData->addPoints($failures, 'Failed');
      $MyData->setPalette('Ok', array(
        'R' => 124,
        'G' => 196,
        'B' => 0,
        'Alpha' => 100,
      ));
      $MyData->setPalette('Failed', array(
        'R' => 255,
        'G' => 40,
        'B' => 0,
        'Alpha' => 100,
      ));
      $MyData->setAxisName(0, 'Time (ms)');
      $MyData->addPoints($methodsNames,' ');
      $MyData->setAbscissa(' ');

      /* Create the pChart object */
      $myPicture = new pImage($chartWidth, $chartHeight, $MyData);
      $myPicture->Antialias = false;
//$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
      $myPicture->drawGradientArea(
        0,
        0,
        $chartWidth,
        $chartHeight,
//DIRECTION_VERTICAL,array("StartR"=>47,"StartG"=>47,"StartB"=>47,"EndR"=>17,"EndG"=>17,"EndB"=>17,"Alpha"=>100
//DIRECTION_VERTICAL,array("StartR"=>67,"StartG"=>67,"StartB"=>67,"EndR"=>37,"EndG"=>37,"EndB"=>37,"Alpha"=>100

        DIRECTION_VERTICAL,
        /*
        array(
          'StartR' => 100,
          'StartG' => 100,
          'StartB' => 100,
          'EndR' => 50,
          'EndG' => 50,
          'EndB' => 50,
          'Alpha' => 100,
        )*/
        array(
          'StartR' => 255,
          'StartG' => 255,
          'StartB' => 255,
          'EndR' => 187,
          'EndG' => 187,
          'EndB' => 187,
          'Alpha' => 100,
        )
      );
      $myPicture->drawFilledRectangle(300, 40, 740, $chartHeight-20, array(
        'R' => 48,
        'G' => 48,
        'B' => 48,
        'Dash' => true,
        'DashR' => 85,
        'DashG' => 85,
        'DashB' => 85,
        'BorderR' => 48,
        'BorderG' => 48,
        'BorderB' => 48,
        //'Surrounding' => -100,
        //'Alpha' => 100,
      ));
      /* Write the picture title */
      $myPicture->setFontProperties(array(
        //'FontName' => CINTIENT_INSTALL_DIR . 'lib/pChart/fonts/MiniSet2.ttf',
        'FontName' => CINTIENT_INSTALL_DIR . 'lib/pChart/fonts/pf_arma_five.ttf',
        'FontSize' => 6,
        'R' => 85,
        'G' => 85,
        'B' => 85,
      ));
      //$myPicture->drawText(10, 13, 'Unit tests for ' . $classXml->attributes()->name);

      /* Draw the scale and the chart */
      $myPicture->setGraphArea(300, 40, 740, $chartHeight-20);
      /*$myPicture->drawFilledRectangle(300, 40, 740, $chartHeight-20, array(
        'R' => 153,
        'G' => 153,
        'B' => 140,
        'Surrounding' => -100,
        'Alpha' => 40,
      ));*/
      $myPicture->setShadow(true, array(
        'X' => 1,
        'Y' => 1,
        'R' => 0,
        'G' => 0,
        'B' => 0,
        'Alpha' => 7,
      ));
      $myPicture->drawScale(array(
        'Pos' => SCALE_POS_TOPBOTTOM,
        'Mode' => SCALE_MODE_ADDALL_START0,
        'DrawSubTicks' => false,
        'MinDivHeight' => 20,
        //'GridTicks' => 5,
        'GridAlpha' => 0,
        'DrawXLines' => false,
        'DrawYLines' => ALL,
        'AxisR' => 85,
        'AxisG' => 85,
        'AxisB' => 85,
        'TickR' => 85,
        'TickG' => 85,
        'TickB' => 85,
        'InnerTickWidth' => 2,
        'CycleBackground' => true,
      ));

//$myPicture->drawThreshold(0,array("WriteCaption"=>false));


      $myPicture->drawStackedBarChart(array(
        'Interleave' => 2,
        'Gradient' => true,
        //'BorderR' => 255,
        //'BorderG' => 255,
        //'BorderB' => 255,
      ));

      /* Write the chart legend */
      $myPicture->drawLegend(15, $chartHeight-15, array(
        'Style' => LEGEND_NOBORDER,
        'Mode' => LEGEND_HORIZONTAL
      ));

      /* Render the picture to file */
      $chartFile = "{$this->getReportsDir()}{$class->getChartFilename()}";
      $myPicture->render($chartFile);
      if (!file_exists($chartFile)) {
        SystemEvent::raise(SystemEvent::ERROR, "Chart file was not saved. [PID={$this->getProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
        return false;
      }

      $class->setTestMethods($methods);
      $classes[] = $class;
      return $classes;
    }
  }

  public function delete()
  {
    $sql = "DROP TABLE projectbuild{$this->getProject()->getId()}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project build table. [TABLE={$this->getProject()->getId()}]", __METHOD__);
      return false;
    }
    return true;
  }

  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    $arr['_project'] = null;
    unset($arr['_project']);
    return md5(serialize($arr));
  }

  public function getReportsDir()
  {
    return $this->getProject()->getReportsWorkingDir() . $this->getId() . '/';
  }

  public function init()
  {
    //
    // Get the ID
    //
    if (!$this->_save()) {
      return false;
    }
    //
    // Create this build's report dir, backing up an existing one
    //
    if (is_dir($this->getReportsDir())) {
      $backupOldBuildReportDir = $this->getReportsDir() . '_old_' . uniqid() . '/';
    }
    if (!mkdir($this->getReportsDir(), DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't create report dir for build. [PID={$this->getProject()->getId()}] [DIR={$this->getReportsDir()}] [BUILD={$this->getId()}]", __METHOD__);
      return false;
    }
    //
    // Backup the original junit report file
    // TODO: only if unit tests were comissioned!!!!
    //
    //if (UNIT_TESTES_WERE_DONE) {
      if (!@copy($this->getProject()->getReportsWorkingDir() . CINTIENT_JUNIT_REPORT_FILENAME, $this->getReportsDir() . CINTIENT_JUNIT_REPORT_FILENAME)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not backup original Junit XML file [PID={$this->getProject()->getId()}] [BUILD={$this->getId()}]", __METHOD__);
      }
    //}
    return true;
  }

  private function _save($force=false)
  {
    if ($this->_getCurrentSignature() == $this->_signature && !$force) {
      SystemEvent::raise(SystemEvent::DEBUG, "Save called, but no saving is required.", __METHOD__);
      return false;
    }
    if (!Database::beginTransaction()) {
      return false;
    }
    $sql = 'REPLACE INTO projectbuild' . $this->getProject()->getId()
         . ' (id, label, description, output, status, scmrevision)'
         . ' VALUES (?,?,?,?,?,?)';
    $val = array(
      $this->getId(),
      $this->getLabel(),
      $this->getDescription(),
      $this->getOutput(),
      $this->getStatus(),
      $this->getScmRevision(),
    );
    if ($this->_id === null) {
      if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
      $this->setId($id);
    } else {
      if (!Database::execute($sql, $val)) {
        Database::rollbackTransaction();
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
        return false;
      }
    }

    if (!Database::endTransaction()) {
      SystemEvent::raise(SystemEvent::ERROR, "Something occurred while finishing transaction. The project build might not have been saved. [PID={$this->getProject()->getId()}]", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved project build. [PID={$this->getProject()->getId()}]", __METHOD__);
    #endif
    $this->updateSignature();
    return true;
  }

  public function updateSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }

  static public function getById($buildId, Project $project, User $user, $access = Access::READ, array $options = array())
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $buildId = (int)$buildId;
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' AND pb.id=?';
    $val = array($project->getId(), $user->getId(), $access, $buildId);
    if ($rs = Database::query($sql, $val)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $project);
      }
    }
    return $ret;
  }

  static public function getLatest(Project $project, User $user, $access = Access::READ, array $options = array())
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?'
         . ' ORDER BY pb.id DESC'
         . ' LIMIT 1';
    $val = array($project->getId(), $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      if ($rs->nextRow()) {
        $ret = self::_getObject($rs, $project);
      }
    }
    return $ret;
  }

  static public function getList(Project $project, User $user, $access = Access::READ, array $options = array())
  {
    isset($options['sort'])?:$options['sort']=Sort::DATE_DESC;
    isset($options['pageStart'])?:$options['pageStart']=0;
    isset($options['pageLength'])?:$options['pageLength']=CINTIENT_BUILDS_PAGE_LENGTH;

    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT pb.*'
         . ' FROM projectbuild' . $project->getId() . ' pb, projectuser pu'
         . ' WHERE pu.projectid=?'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    if ($options['sort'] != Sort::NONE) {
      $sql .= ' ORDER BY';
      switch ($options['sort']) {
        case Sort::DATE_ASC:
          $sql .= ' pb.id ASC';
          break;
        case Sort::DATE_DESC:
          $sql .= ' pb.id DESC';
      }
    }
    $sql .= ' LIMIT ?, ?';
    $val = array($project->getId(), $user->getId(), $access, $options['pageStart'], $options['pageLength']);
    if ($rs = Database::query($sql, $val)) {
      $ret = array();
      while ($rs->nextRow()) {
        $projectBuild = self::_getObject($rs, $project);
        $ret[] = $projectBuild;
      }
    }
    return $ret;
  }

  static private function _getObject(Resultset $rs, Project $project)
  {
    $ret = new ProjectBuild($project);
    $ret->setId($rs->getId());
    $ret->setDate($rs->getDate());
    $ret->setLabel($rs->getLabel());
    $ret->setDescription($rs->getDescription());
    $ret->setOutput($rs->getOutput());
    $ret->setStatus($rs->getStatus());
    $ret->setScmRevision($rs->getScmRevision());

    $ret->updateSignature();
    return $ret;
  }

  static public function install($projectId)
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS projectbuild{$projectId} (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  label VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  output TEXT NOT NULL DEFAULT '',
  status TINYINT UNSIGNED DEFAULT 0,
  scmrevision INTEGER UNSIGNED DEFAULT 0
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems creating table. [TABLE={$projectId}]", __METHOD__);
      return false;
    }
    return true;
  }

  static public function uninstall($projectId)
  {
    $sql = "DROP TABLE projectbuild{$projectId}";
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't delete project build table. [TABLE={$projectId}]", __METHOD__);
      return false;
    }
    return true;
  }
}