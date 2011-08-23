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
 * Mapping rules:
 *
 *   URI          => method name  => template filename:
 *
 * . /foo         => foo()        => foo.tpl         (default section)
 * . /foo-bar     => fooBar()     => foo-bar.tpl     (default section)
 * . /foo/bar     => foo_bar()    => foo/bar.tpl     (foo section)
 * . /foo/foo-bar => foo_fooBar() => foo/foo-bar.tpl (foo section)
 *
 * Smarty variable naming rules:
 *
 * <method_name>_<variable_name>
 * Example: $s->assign('fooBar_foo'); => variable foo for method fooBar()
 *
 *
 * Special account for provider methods. These are not directly called
 * by the WebHandler, but rather by templates themselves. They provide
 * specific data objects that would otherwise have to be fetched on
 * all the mapped methods. These typically allow footer.inc.tpl and
 * header.inc.tpl to call it at this central point.
 *
 * @package View
 */
class TemplateManager
{
  /* +----------------------------------------------------------------+ *\
  |* | TESTS                                                          | *|
  \* +----------------------------------------------------------------+ */

  static public function tests_phpinfo()
  {
    echo phpinfo();
    exit;
  }

  static public function tests_showUser()
  {
    $user = User::getByUsername('matamouros');
    var_dump($user);
    exit;
  }

  static public function tests_tasks()
  {
    $exec = new Build_BuilderElement_Task_Exec();
    $exec->setExecutable('ls');
    $exec->setArgs(array('-la'));
    $exec->setBaseDir('/tmp/');
    //$exec->setOutputProperty('xpto');
    //echo $exec->toString('ant');

    $delete = new Build_BuilderElement_Task_Filesystem_Delete();
    $delete->setIncludeEmptyDirs(true);
    $delete->setFailOnError(true);
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setDir('/tmp/apache');
    //$fileset->setDefaultExcludes(false);
    $fileset->setInclude(array('extra/**/*.conf'));
    $delete->setFilesets(array($fileset));
    //echo $delete->toString('ant');

    $echo = new Build_BuilderElement_Task_Echo();
    $echo->setMessage('Ol�');
    $echo->setFile('${workDir}/ixo.txt');

    $echo2 = new Build_BuilderElement_Task_Echo();
    $echo2->setMessage('About to do an exec2!');
    $echo2->setFile('/tmp/test.log');
    $echo2->setAppend(true);

    $mkdir = new Build_BuilderElement_Task_Filesystem_Mkdir();
    //$mkdir->setDir('/tmp/tmp2/tmp3');
    $mkdir->setDir('${dir}');

    $lint = new Build_BuilderElement_Task_Php_PhpLint();
    $lint->setFilesets(array($fileset));

    $chmod = new Build_BuilderElement_Task_Filesystem_Chmod();
    $chmod->setMode('${perms}');
    $chmod->setFile('${file}');
    //$chmod->setFilesets(array($fileset));

    $chown = new Build_BuilderElement_Task_Filesystem_Chown();
    $chown->setFile('/tmp/lixo1.php');
    $chown->setUser('www-data');

    $copy = new Build_BuilderElement_Task_Filesystem_Copy();
    $copy->setFile('/tmp/src/config/cintient.conf.php');
    $copy->setToDir('${toDir}');

    $perl = new Build_BuilderElement_Task_Perl_PerlSyntax();
    $fs2 = new Build_BuilderElement_Type_Fileset();
    $fs2->setDir('/tmp/');
    $fs2->setInclude(array('**/*pl'));
    $perl->setFilesets(array($fs2));

    /*$fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setDir('${dir}');
    $fileset->setInclude(array('${include}'));

    $fileset->setType(Build_BuilderElement_Type_Fileset::BOTH);
    $copy->setFilesets(array($fileset));
		*/
    $properties = new Build_BuilderElement_Type_Properties();
    $properties->setText("workDir = /tmp/
title = Cintient
executable = ls
args = -la
dir = /tmp/src/
file = /tmp/lixo1.php
perms = 755
include = **/*
toDir = /tmp/src2/
");

    $target = new Build_BuilderElement_Target();
    $target->setName('tests');
    $target->setTasks(array($perl));
    //echo $target->toString('php');

    $target2 = new Build_BuilderElement_Target();
    $target2->setName('tests2');
    //$target->setTasks(array($delete, $exec));
    $target2->setTasks(array($echo, $mkdir));
    //echo $target->toString('php');

    $project = new Build_BuilderElement_Project();
    $project->addTarget($target);
    $project->setBaseDir('/tmp/');
    //$project->addTarget($target2);
    $project->setDefaultTarget($target->getName());
    $code = $project->toPhp();

    echo $code;
    //var_dump(BuilderConnector_Php::execute($code));

    exit;
  }

  static public function tests_pchart1()
  {
    /* @ 700x230 Boundaries writting drawing example. */

    /* pChart library inclusions */
    include 'lib/pChart/class/pData.class';
    include 'lib/pChart/class/pDraw.class';
    include 'lib/pChart/class/pImage.class';

    /* Create and populate the pData object */
    $MyData = new pData();
    $MyData->addPoints(array(2,7,5,18,VOID,12,10,15,8,5,6,9),"Project #1");
    $MyData->setAxisName(0,"# Builds");
    $MyData->addPoints(array("Jan","Feb","Mar","Apr","May","Jun","Jui","Aou","Sep","Oct","Nov","Dec"),"Labels");
    $MyData->setSerieDescription("Labels","Months");
    $MyData->setAbscissa("Labels");

    /* Create the pChart object */
    $myPicture = new pImage(700,230,$MyData);
    $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>100,"StartG"=>100,"StartB"=>100,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));
    $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>100,"StartG"=>100,"StartB"=>100,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>20));
    $myPicture->drawGradientArea(0,0,60,230,DIRECTION_HORIZONTAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

    /* Do some cosmetics */
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
    $myPicture->drawLine(60,0,60,230,array("R"=>70,"G"=>70,"B"=>70));
    $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
    $myPicture->setFontProperties(array("FontName"=>CINTIENT_INSTALL_DIR . "lib/pChart/fonts/Forgotte.ttf","FontSize"=>11));
    $myPicture->drawText(35,115,"Yearly builds",array("R"=>255,"G"=>255,"B"=>255,"FontSize"=>20,"Angle"=>90,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

    /* Draw a spline chart */
    $myPicture->setGraphArea(100,30,680,190);
    $myPicture->drawFilledRectangle(100,30,680,190,array("R"=>255,"G"=>255,"B"=>255,"Alpha"=>20));
    $myPicture->setFontProperties(array("R"=>255,"G"=>255,"B"=>255,"FontName"=>CINTIENT_INSTALL_DIR . "lib/pChart/fonts/pf_arma_five.ttf","FontSize"=>6));
    $myPicture->drawScale(array("AxisR"=>255,"AxisG"=>255,"AxisB"=>255,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE));
    $myPicture->drawSplineChart();

    /* Write the data bounds */
    $myPicture->writeBounds();
    $myPicture->setShadow(FALSE);

    /* Write the chart legend */
    $myPicture->drawLegend(630,215,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

    /* Render the picture (choose the best way) */
    $myPicture->autoOutput("pictures/example.writeBounds.png");
    exit;
  }

  static public function tests_pchart2()
  {
/*
    $xmlStr = <<<EOT
<testsuites>
<testsuite2 name="whaever">
  <testsuite name="DatabaseTest" file="/Users/pfonseca/Dev/cintient/src/tests/DatabaseTest.php" tests="23" assertions="79" failures="0" errors="0" time="0.129403">
    <testsuite name="DatabaseTest::testExecuteNoParamsBinding" tests="5" assertions="15" failures="0" errors="0" time="0.028787">
      <testcase name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      <testcase name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
      <testcase name="testExecuteNoParamsBinding with data set #2" assertions="3" time="0.004264"/>
      <testcase name="testExecuteNoParamsBinding with data set #3" assertions="3" time="0.004306"/>
      <testcase name="testExecuteNoParamsBinding with data set #4" assertions="3" time="0.004747"/>
    </testsuite>
    <testsuite name="DatabaseTest::testQueryNoParamsBinding" tests="1" assertions="3" failures="0" errors="0" time="0.004441">
      <testcase name="testQueryNoParamsBinding with data set #0" assertions="3" time="0.004441"/>
    </testsuite>
    <testsuite name="DatabaseTest::testQueryParamsBinding" tests="1" assertions="3" failures="0" errors="0" time="0.004417">
      <testcase name="testQueryParamsBinding with data set #0" assertions="3" time="0.004417"/>
    </testsuite>
    <testsuite name="DatabaseTest::testQueryParamsBindingExcessValues" tests="1" assertions="3" failures="0" errors="0" time="0.005673">
      <testcase name="testQueryParamsBindingExcessValues with data set #0" assertions="3" time="0.005673"/>
    </testsuite>
  </testsuite>
</testsuite2>
</testsuites>
EOT;
*/
    $xmlStr = <<<EOT
<aa>
  <aa1>
    <aa2 name="DatabaseTest" file="/Users/pfonseca/Dev/cintient/src/tests/DatabaseTest.php" tests="23" assertions="79" failures="0" errors="0" time="0.129403">
      <aa3 name="DatabaseTest::testExecuteNoParamsBinding" tests="5" assertions="15" failures="3" errors="0" time="0.028787">
        <aa41 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
        <aa42 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa43 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa44 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa45 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa46 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
      </aa3>
      <aa4 name="DatabaseTest::testExecuteParamsBinding" tests="5" assertions="12" failures="1" errors="0" time="0.010916">
        <aa41 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
        <aa42 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa43 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa44 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
      </aa4>
      <aa5 name="DatabaseTest::testQuery" tests="5" assertions="4" failures="0" errors="0" time="0.004554">
        <aa51 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
        <aa52 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa53 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
        <aa54 name="testExecuteNoParamsBinding with data set #1" assertions="3" time="0.004554"/>
      </aa5>
      <aa6 name="DatabaseTest::testInsert" tests="5" assertions="7" failures="6" errors="0" time="0.014587">
        <aa61 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </aa6>
      <aa7 name="DatabaseTest::testBeginTransaction" tests="5" assertions="25" failures="4" errors="0" time="0.023787">
        <aa71 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </aa7>
      <aa6 name="DatabaseTest::testInsert" tests="5" assertions="7" failures="6" errors="0" time="0.014587">
        <aa61 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </aa6>
      <aa7 name="DatabaseTest::testBeginTransaction" tests="5" assertions="25" failures="4" errors="0" time="0.023787">
        <aa71 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </aa7>
    </aa2>
    <ab2 name="DatabaseTest" file="/Users/pfonseca/Dev/cintient/src/tests/DatabaseTest.php" tests="23" assertions="79" failures="0" errors="0" time="0.129403">
      <ab6 name="DatabaseTest::testInsert" tests="5" assertions="7" failures="6" errors="0" time="0.018787">
        <ab61 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </ab6>
      <ab7 name="DatabaseTest::testBeginTransaction" tests="5" assertions="25" failures="4" errors="0" time="0.028787">
        <ab71 name="testExecuteNoParamsBinding with data set #0" assertions="3" time="0.010916"/>
      </ab7>
    </ab2>
  </aa1>
</aa>
EOT;
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
    $xml = new SimpleXMLElement($xmlStr);
    $classes = array();
    $xmls = $xml->children();
    /*
    foreach ($xmls as $node) {
      $getRootNode = f($node);
      $classXml = f($node);
      $class = new TestClass();
      $class->setName($classXml->getName());
      $class->setFile((string)$classXml->attributes()->file);
      $class->setTests((string)$classXml->attributes()->tests);
      $class->setAssertions((string)$classXml->attributes()->assertions);
      $class->setFailures((string)$classXml->attributes()->failures);
      $class->setErrors((string)$classXml->attributes()->errors);
      $class->setTime((string)$classXml->attributes()->time);

      $methods = array();
      foreach ($classXml->children() as $methodXml) {
        $method = new TestMethod();
        $method->setName($methodXml->getName());
        $method->setTests((string)$methodXml->attributes()->tests);
        $method->setAssertions((string)$methodXml->attributes()->assertions);
        $method->setFailures((string)$methodXml->attributes()->failures);
        $method->setErrors((string)$methodXml->attributes()->errors);
        $method->setTime((string)$methodXml->attributes()->time);

        $cases = array();
        foreach ($methodXml->children() as $caseXml) {
          $case = new TestCase();
          $case->setName((string)$caseXml->getName());
          $case->setAssertions((string)$caseXml->attributes()->assertions);
          $case->setTime((string)$caseXml->attributes()->time);
          $cases[] = $case;
        }
        $method->setTestCases($cases);
        $methods[] = $method;
      }
      $class->setTestMethods($methods);
      $classes[] = $class;
    }*/




    foreach ($xmls as $node) {
      $assertions = 0;  // total number of "test" points
      $successes = array(); // assertions - failures
      $failures = array();
      $testMethods = array();
      $classXml = f($node);
      $class = new TestClass();
      $class->setName($classXml->getName());
      $class->setFile((string)$classXml->attributes()->file);
      $class->setTests((string)$classXml->attributes()->tests);
      $class->setAssertions((string)$classXml->attributes()->assertions);
      $class->setFailures((string)$classXml->attributes()->failures);
      $class->setErrors((string)$classXml->attributes()->errors);
      $class->setTime((string)$classXml->attributes()->time);

      $methods = array();
      $i = 0;

      foreach ($classXml->children() as $methodXml) {
        $method = new TestMethod();
        $method->setName($methodXml->getName());
        $method->setTests((string)$methodXml->attributes()->tests);
        $method->setAssertions((string)$methodXml->attributes()->assertions);
        $method->setFailures((string)$methodXml->attributes()->failures);
        $method->setErrors((string)$methodXml->attributes()->errors);
        $method->setTime((string)$methodXml->attributes()->time);

        /*
        $cases = array();
        foreach ($methodXml->children() as $caseXml) {
          $case = new TestCase();
          $case->setName((string)$caseXml->getName());
          $case->setAssertions((string)$caseXml->attributes()->assertions);
          $case->setTime((string)$caseXml->attributes()->time);
          $cases[] = $case;
        }
        $method->setTestCases($cases);
        */

        $methods[] = $method;
        $time = (float)$methodXml->attributes()->time * 1000; // to milliseconds

        $testMethods[] = $methodXml->attributes()->name;
        $f = ((((float)$methodXml->attributes()->failures) * $time) / (float)$methodXml->attributes()->assertions);
        $successes[] = (float)$time - (float)$f;
        $failures[] = $f;
        $i++;
      }

      $chartWidth = 700;
      if ($i == 1) {
        $i++;
      }
      $chartHeight = 25 * $i + 60;

      /* pChart library inclusions */
      include 'lib/pChart/class/pData.class';
      include 'lib/pChart/class/pDraw.class';
      include 'lib/pChart/class/pImage.class';

      $MyData = new pData();
      $MyData->addPoints($successes, "Ok");
      $MyData->addPoints($failures, "Fail");
      $MyData->setPalette("Ok",array("R"=>124,"G"=>196,"B"=>0,"Alpha"=>100));
      $MyData->setPalette("Fail",array("R"=>254,"G"=>15,"B"=>0,"Alpha"=>100));
      $MyData->setAxisName(0,"Time (ms)");
      //$MyData->setAxisUnit(0,"ms");
      $MyData->addPoints($testMethods," ");
      $MyData->setAbscissa(" ");

      /* Create the pChart object */
      //
      // ~40px for each test
      //
      $myPicture = new pImage($chartWidth, $chartHeight, $MyData);
      $myPicture->Antialias = false;
      $myPicture->drawGradientArea(0,0,$chartWidth,$chartHeight,DIRECTION_VERTICAL,array("StartR"=>100,"StartG"=>100,"StartB"=>100,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

      /* Write the picture title */
      $myPicture->setFontProperties(array("FontName"=>CINTIENT_INSTALL_DIR . "lib/pChart/fonts/pf_arma_five.ttf","FontSize"=>6, "R"=>255,"G"=>255,"B"=>255));
      $myPicture->drawText(10,13,"Unit tests on " . $classXml->getName(),array("R"=>255,"G"=>255,"B"=>255));

      /* Draw the scale and the chart */
      $myPicture->setGraphArea(240,40,640,$chartHeight-20);
      $myPicture->drawFilledRectangle(240,40,640,$chartHeight-20,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-100,"Alpha"=>10));
      $myPicture->drawScale(array(
        "Pos" => SCALE_POS_TOPBOTTOM,
        "Mode" => SCALE_MODE_ADDALL,
        "DrawSubTicks" => true,
        "MinDivHeight" => 20,
      /*
        'DrawXLines' => false,
        'DrawYLines' => NONE,
      */
        'GridTicks' => 2,
        'DrawXLines' => true,
        'DrawYLines' => ALL,

      ));
      $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
      $myPicture->drawStackedBarChart(array(
        "Interleave"=>2,
        "Gradient"=>false,
      ));
      $myPicture->drawLegend(15, $chartHeight-15,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
      $myPicture->setShadow(FALSE);

      /* Write the chart legend */
      //$myPicture->drawLegend(510,205,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

      /* Render the picture (choose the best way) */
      $myPicture->autoOutput("pictures/example.drawStackedBarChart.png");





      $class->setTestMethods($methods);
      $classes[] = $class;
    }






    //
    // We're exactly at the test class (file) root level, with level 1 being
    // the unit test (method of the original class) and level 2 being
    // the various datasets used in the test (each a test case).
    //



    /* @ 700x230 Stacked bar chart drawing example. */


    exit;
  }

  static public function tests_pchart3()
  {
    /* @ 700x230 Filled spline chart drawing example. */

    /* pChart library inclusions */
      include 'lib/pChart/class/pData.class';
      include 'lib/pChart/class/pDraw.class';
      include 'lib/pChart/class/pImage.class';

    /* Create and populate the pData object */
    $MyData = new pData();
    $MyData->setAxisName(0,"Strength");
    for($i=0;$i<=720;$i=$i+20)
    {
     $MyData->addPoints(cos(deg2rad($i))*100,"Probe 1");
     $MyData->addPoints(cos(deg2rad($i+90))*60,"Probe 2");
    }

    /* Create the pChart object */
    $myPicture = new pImage(847,304,$MyData);
    $myPicture->drawGradientArea(0,0,847,304,DIRECTION_VERTICAL,array("StartR"=>47,"StartG"=>47,"StartB"=>47,"EndR"=>17,"EndG"=>17,"EndB"=>17,"Alpha"=>100));
    $myPicture->drawGradientArea(0,250,847,304,DIRECTION_VERTICAL,array("StartR"=>47,"StartG"=>47,"StartB"=>47,"EndR"=>27,"EndG"=>27,"EndB"=>27,"Alpha"=>100));
    $myPicture->drawLine(0,249,847,249,array("R"=>0,"G"=>0,"B"=>0));
    $myPicture->drawLine(0,250,847,250,array("R"=>70,"G"=>70,"B"=>70));

    /* Add a border to the picture */
    $myPicture->drawRectangle(0,0,846,303,array("R"=>204,"G"=>204,"B"=>204));

    /* Write the picture title */
    $myPicture->setFontProperties(array("FontName"=>CINTIENT_INSTALL_DIR . "lib/pChart/fonts/pf_arma_five.ttf","FontSize"=>6));
    $myPicture->drawText(423,14,"Cyclic magnetic field strength",array("R"=>255,"G"=>255,"B"=>255,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));

    /* Define the chart area */
    $myPicture->setGraphArea(58,27,816,228);

    /* Draw a rectangle */
    $myPicture->drawFilledRectangle(58,27,816,228,array("R"=>0,"G"=>0,"B"=>0,"Dash"=>TRUE,"DashR"=>0,"DashG"=>51,"DashB"=>51,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));

    /* Turn on shadow computing */
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

    /* Draw the scale */
    $myPicture->setFontProperties(array("R"=>255,"G"=>255,"B"=>255));
    $ScaleSettings = array("XMargin"=>4,"DrawSubTicks"=>TRUE,"GridR"=>255,"GridG"=>255,"GridB"=>255,"AxisR"=>255,"AxisG"=>255,"AxisB"=>255,"GridAlpha"=>30,"CycleBackground"=>TRUE);
    $myPicture->drawScale($ScaleSettings);

    /* Draw the spline chart */
    $myPicture->drawFilledSplineChart();

    /* Write the chart boundaries */
    $BoundsSettings = array("MaxDisplayR"=>237,"MaxDisplayG"=>23,"MaxDisplayB"=>48,"MinDisplayR"=>23,"MinDisplayG"=>144,"MinDisplayB"=>237);
    $myPicture->writeBounds(BOUND_BOTH,$BoundsSettings);

    /* Write the 0 line */
    $myPicture->drawThreshold(0,array("WriteCaption"=>TRUE));

    /* Write the chart legend */
    $myPicture->setFontProperties(array("R"=>255,"G"=>255,"B"=>255));
    $myPicture->drawLegend(560,266,array("Style"=>LEGEND_NOBORDER));

    /* Write the 1st data series statistics */
    $Settings = array("R"=>188,"G"=>224,"B"=>46,"Align"=>TEXT_ALIGN_BOTTOMLEFT);
    $myPicture->drawText(620,270,"Max : ".ceil($MyData->getMax("Probe 1")),$Settings);
    $myPicture->drawText(680,270,"Min : ".ceil($MyData->getMin("Probe 1")),$Settings);
    $myPicture->drawText(740,270,"Avg : ".ceil($MyData->getSerieAverage("Probe 1")),$Settings);

    /* Write the 2nd data series statistics */
    $Settings = array("R"=>224,"G"=>100,"B"=>46,"Align"=>TEXT_ALIGN_BOTTOMLEFT);
    $myPicture->drawText(620,283,"Max : ".ceil($MyData->getMax("Probe 2")),$Settings);
    $myPicture->drawText(680,283,"Min : ".ceil($MyData->getMin("Probe 2")),$Settings);
    $myPicture->drawText(740,283,"Avg : ".ceil($MyData->getSerieAverage("Probe 2")),$Settings);

    /* Render the picture (choose the best way) */
    $myPicture->autoOutput("pictures/example.drawFilledSplineChart.png");
  }

  static public function tests_system()
  {
    $s = new SystemSettings();
    $s->setSetting('test2', true);
    //$s->setSetting('test', true);
    $s->setSetting('allowUserSignUp', true);
    exit;
  }


  /* +----------------------------------------------------------------+ *\
  |* | DEFAULT                                                        | *|
  \* +----------------------------------------------------------------+ */

  static public function asset()
  {
    if (!isset($_GET['f']) || empty($_GET['f'])) {
      SystemEvent::raise(SystemEvent::INFO, "Missing required parameters.", __METHOD__);
      //TODO: Redirect and exit?
      exit;
    }

    //
    // Avatar
    //
    if (isset($_GET['avatar'])) {
      $filename = CINTIENT_AVATARS_DIR . $_GET['f'];
      if (!file_exists($filename)) {
        SystemEvent::raise(SystemEvent::INFO, "Requested asset not found. [ASSET=avatar] [FILE={$filename}]", __METHOD__);
        //TODO: Redirect and exit?
        exit;
      }
      $pos = strrpos($filename, '.');
      if ($pos) {
        $extension   = strtolower(substr($filename, $pos+1));
        $ext['jpg']  = 'image/jpeg';
        $ext['jpeg'] = 'image/jpeg';
        $ext['gif']  = 'image/gif';
        $ext['png']  = 'image/png';
        if (isset($ext[$extension])) {
          header('Content-type: '.$ext[$extension]);
        }
      }
      header('Last-Modified: '.date('r',filectime($filename)));
      header('Content-Length: '.filesize($filename));
      readfile($filename);

    //
    // It's a project build asset!
    //
    } elseif (isset($_GET['bid']) && !empty($_GET['bid'])) {
      if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
        SystemEvent::raise(SystemEvent::INFO, "Problems fetching requested project.", __METHOD__);
        //TODO: Redirect and exit?
        exit;
      }
      $build = null;
      $build = Project_Build::getById($_GET['bid'], $GLOBALS['project'], $GLOBALS['user']);
      if (!($build instanceof Project_Build)) {
        SystemEvent::raise(SystemEvent::INFO, "Could not access the specified project build. [PID={$GLOBALS['project']->getId()}] [BID={$build->getId()}]", __METHOD__);
        //TODO: Redirect and exit?
        exit;
      }
      if (!empty($_GET['cc'])) { // code coverage asset
        $filename = $build->getBuildDir() . CINTIENT_CODECOVERAGE_HTML_DIR . $_GET['f'];
      } else { // Normal file
        $filename = $build->getBuildDir() . $_GET['f'];
      }
      if (!file_exists($filename)) {
        SystemEvent::raise(SystemEvent::INFO, "Requested asset not found. [PID={$GLOBALS['project']->getId()}] [BID={$build->getId()}] [FILE={$filename}]", __METHOD__);
        //TODO: Redirect and exit?
        exit;
      }
      $pos = strrpos($filename, '.');
      if ($pos) {
        $extension   = strtolower(substr($filename, $pos+1));
        $ext['jpg']  = 'image/jpeg';
        $ext['jpeg'] = 'image/jpeg';
        $ext['gif']  = 'image/gif';
        $ext['png']  = 'image/png';
        $ext['swf']  = 'application/x-shockwave-flash';
        $ext['css']  = 'text/css';
        $ext['xml']  = 'text/xml';
        $ext['svg']  = 'image/svg+xml';
        if (isset($ext[$extension])) {
          header('Content-type: '.$ext[$extension]);
        }
      }
      header('Last-Modified: '.date('r',filectime($filename)));
      header('Content-Length: '.filesize($filename));
      readfile($filename);
    }
    exit;
  }

  static public function authentication()
  {
    if (isset($GLOBALS['user']) && $GLOBALS['user'] instanceof User) {
      header("Location: " . UrlManager::getForDashboard());
      exit;
    }
  }

  /**
   * Shows a list of available projects to the current user, for selection. Any
   * project for which the user has at least read access level.
   */
  static public function dashboard()
  {
    $GLOBALS['smarty']->assign('dashboard_projectList', Project::getList($GLOBALS['user'], Access::READ));
  }

  static public function install()
  {
    session_destroy();

    //
    // Create necessary dirs
    //
    if (!file_exists(CINTIENT_WORK_DIR) && !mkdir(CINTIENT_WORK_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create working dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!file_exists(CINTIENT_PROJECTS_DIR) && !mkdir(CINTIENT_PROJECTS_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create projects dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!file_exists(CINTIENT_ASSETS_DIR) && !mkdir(CINTIENT_ASSETS_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create assets dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!file_exists(CINTIENT_AVATARS_DIR) && !mkdir(CINTIENT_AVATARS_DIR, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not create avatars dir. Check your permissions.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    //
    // Setup all objects
    //
    if (!User::install()) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not setup User object.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!Project::install()) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not setup Project object.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    if (!SystemSettings::install()) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not setup SystemSettings object.", __METHOD__);
      echo "Error"; // TODO: treat this properly
      exit;
    }
    //
    // Test user setup
    //
    $user = new User();
    $user->setEmail('pedro.matamouros@gmail.com');
    $user->setNotificationEmails('pedro.matamouros@gmail.com,');
    $user->setName('Pedro Mata-Mouros');
    $user->setUsername('matamouros');
    $user->setCos(UserCos::ROOT);
    $user->init();
    $user->setPassword('pedro');
    header('Location: ' . UrlManager::getForDashboard());
    exit;
  }

  static public function logout()
  {
    Auth::logout();
    header('Location: ' . CINTIENT_BASE_URL . '/');
    exit;
  }

  static public function notFound()
  {}

  static public function notAuthorized()
  {}

  static public function project()
  {
    //
    // Setting a new project?
    //
    if (isset($_GET['pid']) && !empty($_GET['pid'])) {
      $GLOBALS['project'] = Project::getById($GLOBALS['user'], $_GET['pid']);
    }
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems fetching requested project.", __METHOD__);
      //
      // TODO: Notification
      //
      //
      // TODO: this should really be a redirect to the previous page.
      //
      Redirector::redirectToUri(UrlManager::getForDashboard());
      exit;
    }
    $_SESSION['projectId'] = $GLOBALS['project']->getId();

    $GLOBALS['smarty']->assign('project_buildStats', Project_Build::getStats($GLOBALS['project'], $GLOBALS['user']));
    $GLOBALS['smarty']->assign('project_log', Project_Log::getList($GLOBALS['project'], $GLOBALS['user']));
    $GLOBALS['smarty']->assign('project_buildList', Project_Build::getList($GLOBALS['project'], $GLOBALS['user']));
    $GLOBALS['smarty']->assign('project_build', Project_Build::getLatest($GLOBALS['project'], $GLOBALS['user']));
  }

  static public function project_edit()
  {
    if (!$GLOBALS['project']->userHasAccessLevel($GLOBALS['user'], Access::WRITE) && !$GLOBALS['user']->hasCos(UserCos::ROOT)) {
      //
      //TODO: Notify user and redirect
      //
      SystemEvent::raise(SystemEvent::INFO, "User not authorized to edit project. [USER={$GLOBALS['user']->getUsername()}]", __METHOD__);
      Redirector::redirectToUri(UrlManager::getForProjectView());
      exit;
    }

    //
    // Edit form submission
    //
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      //
      // Check for mandatory attributes
      //
      if (!isset($_POST['title']) ||
           empty($_POST['title']) ||
          !isset($_POST['buildLabel']) ||
           empty($_POST['buildLabel']) ||
          //!isset($_POST['description']) ||
          // empty($_POST['description']) ||
          !isset($_POST['scmConnectorType']) ||
           empty($_POST['scmConnectorType']) ||
          !isset($_POST['scmRemoteRepository']) ||
           empty($_POST['scmRemoteRepository']) ||
          !isset($_POST['scmUsername']) ||
           empty($_POST['scmUsername']) /*||
          !isset($_POST['scmPassword']) ||
           empty($_POST['scmPassword'])*/
      ) {
        //
        // TODO: Error notification!!!
        //
        SystemEvent::raise(SystemEvent::DEBUG, "Project editing failed, required attributes were empty.", __METHOD__);
        $formData = array();
        $formData['title'] = $_POST['title'];
        $formData['buildLabel'] = $_POST['buildLabel'];
        $formData['description'] = $_POST['description'];
        $formData['scmConnectorType'] = $_POST['scmConnectorType'];
        $formData['scmRemoteRepository'] = $_POST['scmRemoteRepository'];
        $formData['scmUsername'] = $_POST['scmUsername'];
        $formData['scmPassword'] = $_POST['scmPassword'];
        $GLOBALS['smarty']->assign('formData', $formData);
        $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
      } else {
        if (!($GLOBALS['project'] instanceof Project)) {
          SystemEvent::raise(SystemEvent::ERROR, "Editing project not possible, because of probable session expire.", __METHOD__);
          return false;
        }
        $project = $GLOBALS['project'];
        $project->setId($GLOBALS['project']->getId());
        $project->setTitle($_POST['title']);
        $project->setBuildLabel($_POST['buildLabel']);
        $project->setDescription($_POST['description']);
        $project->setScmConnectorType($_POST['scmConnectorType']);
        $project->setScmRemoteRepository($_POST['scmRemoteRepository']);
        $project->setScmUsername($_POST['scmUsername']);
        $project->setScmPassword($_POST['scmPassword']);
        $project->addToUsers(
          $GLOBALS['user'],
          Access::OWNER
        );
        $GLOBALS['project'] = $project;
        $GLOBALS['project']->log("Project edited.");
      }

      //
      // We will leave this control and carry on to the project view
      //
    }
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems fetching requested project.", __METHOD__);
      //
      // TODO: Notification
      //
      //
      // TODO: this should really be a redirect to the previous page.
      //
      return false;
    }

    $formData = array();
    $formData['title'] = $GLOBALS['project']->getTitle();
    $formData['buildLabel'] = $GLOBALS['project']->getBuildLabel();
    $formData['description'] = $GLOBALS['project']->getDescription();
    $formData['scmConnectorType'] = $GLOBALS['project']->getScmConnectorType();
    $formData['scmRemoteRepository'] = $GLOBALS['project']->getScmRemoteRepository();
    $formData['scmUsername'] = $GLOBALS['project']->getScmUsername();
    $formData['scmPassword'] = $GLOBALS['project']->getScmPassword();
    $GLOBALS['smarty']->assign('formData', $formData);
    $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
  }

  static public function project_history()
  {
    if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems fetching requested project.", __METHOD__);
      //
      // TODO: Notification
      //
      //
      // TODO: this should really be a redirect to the previous page.
      //
      return false;
    }
    //
    // Viewing project build details
    //
    $build = null; // It's possible that no build was triggered yet.
    if (isset($_GET['bid']) && !empty($_GET['bid'])) {
      $build = Project_Build::getById($_GET['bid'], $GLOBALS['project'], $GLOBALS['user']);
    } else {
      $build = Project_Build::getLatest($GLOBALS['project'], $GLOBALS['user']);
    }

    //
    // TODO: don't let user access the build history of a still unfinished build!
    //

    if ($build instanceof Project_Build) {
      //
      // Special tasks. This is post build, so we're fetching an existing special task (never creating it)
      //
      $specialTasks = $build->getSpecialTasks();
      $GLOBALS['smarty']->assign('project_specialTasks', $specialTasks);
      if (!empty($specialTasks)) {
        foreach ($specialTasks as $task) {
          if (!class_exists($task)) {
            SystemEvent::raise(SystemEvent::ERROR, "Unexisting special task. [PID={$GLOBALS['project']->getId()}] [BUILD={$build->getId()}] [TASK={$task}]", __METHOD__);
            continue;
          }
          $o = $task::getById($build, $GLOBALS['user'], Access::READ);
          //$GLOBALS['smarty']->assign($task, $o); // Register for s
          if (!($o instanceof Build_SpecialTaskInterface)) {
            SystemEvent::raise(SystemEvent::ERROR, "Unexisting special task ID. [PID={$GLOBALS['project']->getId()}] [BUILD={$build->getId()}] [TASK={$task}] [TASKID={$build->getId()}]", __METHOD__);
            continue;
          }
          $viewData = $o->getViewData();
          foreach($viewData as $key => $value) {
            $GLOBALS['smarty']->assign($key, $value);
          }
          $o = null;
          unset($o);
        }
      }
    }

    // Last assignments
    $GLOBALS['smarty']->assign('project_buildList', Project_Build::getList($GLOBALS['project'], $GLOBALS['user']));
    $GLOBALS['smarty']->assign('project_build', $build);
  }

  static public function project_new()
  {
    //
    // New project form submission
    //
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      //
      // Check for mandatory attributes
      //
      if (!isset($_POST['title']) ||
           empty($_POST['title']) ||
          !isset($_POST['buildLabel']) ||
           empty($_POST['buildLabel']) ||
          //!isset($_POST['description']) ||
          // empty($_POST['description']) ||
          !isset($_POST['scmConnectorType']) ||
           empty($_POST['scmConnectorType']) ||
          !isset($_POST['scmRemoteRepository']) ||
           empty($_POST['scmRemoteRepository']) ||
          !isset($_POST['scmUsername']) ||
           empty($_POST['scmUsername']) ||
          !isset($_POST['scmPassword']) ||
           empty($_POST['scmPassword'])
      ) {
        //
        // TODO: Error notification!!!
        //
        SystemEvent::raise(SystemEvent::DEBUG, "Project creation failed, required attributes were empty.", __METHOD__);
        $formData = array();
        $formData['title'] = $_POST['title'];
        $formData['buildLabel'] = $_POST['buildLabel'];
        $formData['description'] = $_POST['description'];
        $formData['scmConnectorType'] = $_POST['scmConnectorType'];
        $formData['scmRemoteRepository'] = $_POST['scmRemoteRepository'];
        $formData['scmUsername'] = $_POST['scmUsername'];
        $formData['scmPassword'] = $_POST['scmPassword'];
        $GLOBALS['smarty']->assign('formData', $formData);
        $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
      } else {
        $GLOBALS['project'] = null;
        $project = new Project();
        $project->setTitle($_POST['title']);
        $project->setBuildLabel($_POST['buildLabel']);
        $project->setDescription($_POST['description']);
        $project->setScmConnectorType($_POST['scmConnectorType']);
        $project->setScmRemoteRepository($_POST['scmRemoteRepository']);
        $project->setScmUsername($_POST['scmUsername']);
        $project->setScmPassword($_POST['scmPassword']);
        $project->addToUsers(
          $GLOBALS['user'],
          Access::OWNER
        );

        if (!$project->init()) {
          SystemEvent::raise(SystemEvent::ERROR, "Could not initialize project. Try again later.", __METHOD__);
          //
          // TODO: Notification
          //
          header('Location: ' . UrlManager::getForProjectNew());
          exit;
        }
        $GLOBALS['project'] = $project;
        $_SESSION['projectId'] = $GLOBALS['project']->getId();
        $GLOBALS['project']->log("Project created.");
        Redirector::redirectToUri(UrlManager::getForDashboard());
        exit;
      }
    } else {
      $GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
    }
  }

  /**
   * Provider method for getting all builder elements nicely fit on an
   * array. Originally intended for the builders setup of project edit.
   */
  static public function providerAvailableBuilderElements()
  {
    $dir = CINTIENT_INSTALL_DIR . 'src/core/Build/BuilderElement/';
    $elements = array();
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    //
    // TODO: Disabling support for Target and Project at the moment, for simplification purposes.
    //
    $exceptions = array('**/Target.php', '**/Project.php', '**/Fileset.php');
    foreach (new Framework_FilesystemFilterIterator($it, $dir, array('**/*'), $exceptions) as $entry) {
      $levels = explode('/', substr($entry, strlen($dir)));
      $basename = basename($entry);
      $levelsPath = '$elements';
      if (strrpos($basename, '.php') !== false) {
        foreach ($levels as $level) {
          if ($basename != $level) {
            $levelsPath .= "['$level']";
          } else {
            $name = substr($basename, 0 , strrpos($basename, '.php'));
            $levelsPath .= "['$name'] = '$name';";
          }
        }
      } else {
        foreach ($levels as $level) {
          if ($basename != $level) {
            $levelsPath .= "['$level']";
          } else {
            $levelsPath .= "['$level'] = array();";
          }
        }
      }
      eval($levelsPath);
    }
    $it = null;
    unset($it);

    // Closure to help sort this multi-dimensional array with the tasks.
    // It makes sure that all tasks/subtasks are properly sorted.
    $f = function ($arr) use (&$f) {
      foreach ($arr as $key => $value) {
        if (is_array($value)) {
          $v = call_user_func($f, $value);
          ksort($v, SORT_STRING);
          $arr[$key] = $v;
        }
      }
      return $arr;
    };
    $elements = $f($elements);

    $GLOBALS['smarty']->assign('providerAvailableBuilderElements_elements', $elements);
  }

  /**
   * Provider method for installation stats to footer.inc.tpl. See this
   * file's header for more details on provider methods.
   */
  static public function providerInstallationStats()
  {
    $user = User::getById(1);
    $GLOBALS['smarty']->assign('providerInstallationStats_installDate', $user->getCreationDate());

    //
    // SQL here... :-/ Actually, it'd be nice to cache this...
    //
    $projectsCount = 0;
    $sql = "SELECT COUNT(id) AS num FROM project";
    $rs = Database::query($sql);
    if ($rs !== false && $rs->nextRow()) {
      $projectsCount = (int)$rs->getNum();
    }
    $GLOBALS['smarty']->assign('providerInstallationStats_projectsCount', $projectsCount);

    $usersCount = 0;
    $sql = "SELECT COUNT(id) AS num FROM user";
    $rs = Database::query($sql);
    if ($rs !== false && $rs->nextRow()) {
      $usersCount = (int)$rs->getNum();
    }
    $GLOBALS['smarty']->assign('providerInstallationStats_usersCount', $usersCount);

    $buildsCount = 14;
    //
    // TODO: get an aggregated table that gets updated with the number of builds done
    //
    /*
    $sql = "SELECT COUNT(id) AS num FROM project";
    $rs = Database::query($sql);
    if ($rs !== false && $rs->nextRow()) {
      $projectsCount = (int)$rs->getNum();
    }
    */
    $GLOBALS['smarty']->assign('providerInstallationStats_buildsCount', $buildsCount);
  }

  static public function registration()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      //
      // Check for validity
      //
      $error = false;
      if (!isset($_POST['name']) ||
           empty($_POST['name']) ||
          !isset($_POST['email']) ||
           empty($_POST['email']) ||
          !isset($_POST['username']) ||
           empty($_POST['username']) ||
          !isset($_POST['password']) ||
           empty($_POST['password']) ||
          !isset($_POST['password2']) ||
           empty($_POST['password2']) ||
           $_POST['password'] != $_POST['password2']
      ) {
        SystemEvent::raise(SystemEvent::DEBUG, "User registration failed, required attributes were empty.", __METHOD__);
        $error = true;
      } else {
        $user = User::getByUsername($_POST['username']);
        if ($user instanceof User) {
          SystemEvent::raise(SystemEvent::DEBUG, "Username already taken.", __METHOD__);
          $error = true;
        }
        $user = null;
        unset($user);
      }
      if ($error) {
        //
        // TODO: Error notification!!!
        //
        $formData = array();
        $formData['name'] = $_POST['name'];
        $formData['email'] = $_POST['email'];
        $formData['username'] = $_POST['username'];
        $GLOBALS['smarty']->assign('formData', $formData);
      } else {
        //
        // Everything ok, let's register the new user
        //
        $user = new User();
        $user->setEmail($_POST['email']);
        $user->setNotificationEmails($_POST['email']);
        $user->setName($_POST['name']);
        $user->setUsername($_POST['username']);
        $user->setCos(UserCos::USER);
        $user->init();
        $user->setPassword($_POST['password']);
        //
        // Log the user in
        //
        Auth::authenticate();
        Redirector::redirectToUri(UrlManager::getForDashboard());
        exit;
      }
    }
  }

  static public function settings()
  {

  }
}