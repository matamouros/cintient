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

require '../lib/Smarty-3.1.14/Smarty.class.php';

class HttpController extends Sentient\Object
{
	private function _render()
	{	
		$GLOBALS['templateFile'] = $GLOBALS['subSection'] . '.tpl';
		if ($GLOBALS['section'] != 'default') {
			$GLOBALS['templateFile'] = $GLOBALS['section'] . '/' . $GLOBALS['subSection'] . '.tpl';
		}

		$GLOBALS['smarty']->assign('globals_settings', $GLOBALS['settings']);
		$GLOBALS['smarty']->assign('globals_section', $GLOBALS['section']);
		$GLOBALS['smarty']->assign('globals_subSection', $GLOBALS['subSection']);
		$GLOBALS['smarty']->assign('globals_user', $GLOBALS['user']);
		$GLOBALS['smarty']->assign('globals_project', $GLOBALS['project']);
		$this->providerInstallationStats(); // To be used in the footer
		ob_end_clean();
		$GLOBALS['smarty']->display($GLOBALS['templateFile']);
	}
	
	public function init()
	{
		//
		// Smarty
		//
		$GLOBALS['smarty'] = new Smarty();
		// $GLOBALS['smarty']->setAllowPhpTag(TRUE);
		$GLOBALS['smarty']->setCacheLifetime(0);
		$GLOBALS['smarty']->setDebugging($GLOBALS['config']->valueForKey('smarty.debug'));
		$GLOBALS['smarty']->setForceCompile($GLOBALS['config']->valueForKey('smarty.force_compile'));
		$GLOBALS['smarty']->setCompileCheck($GLOBALS['config']->valueForKey('smarty.compile_check'));
		$GLOBALS['smarty']->setTemplateDir($GLOBALS['config']->valueForKey('smarty.template_dir'));
		$GLOBALS['smarty']->setCompileDir($GLOBALS['config']->valueForKey('smarty.compile_dir'));
		$GLOBALS['smarty']->error_reporting = error_reporting();
		Framework_SmartyPlugin::init($GLOBALS['smarty']);
		
		// Setup template load logic
		if (preg_match('/^\/(?:([\w-]+)\/(?:([\w-]+)\/)?)?$/', $GLOBALS['uri'], $matches)) {
			if (count($matches) == 1) {
				$GLOBALS['section'] = 'default';
				$GLOBALS['subSection'] = 'dashboard';
			} elseif (count($matches) == 2) {
				$GLOBALS['section'] = 'default';
				$GLOBALS['subSection'] = $matches[1];
			} else {
				$GLOBALS['section'] = $matches[1];
				$GLOBALS['subSection'] = $matches[2];
			}
		}
	}
	
	public function run()
	{
		//
		// Authentication
		//
		if ((!isset($GLOBALS['user']) || !($GLOBALS['user'] instanceof User)) &&
			($GLOBALS['subSection'] != 'registration' || !$GLOBALS['settings'][SystemSettings::ALLOW_USER_REGISTRATION]))
		{
			SystemEvent::raise(SystemEvent::INFO, "Authentication required. [URI={$GLOBALS['uri']}]", "webHandler");
			//
			// Special case of template logic here, because the URI will get overwritten
			// right after it. Somewhere, a cute small kitten died a horrible death.
			//
			if (strlen($GLOBALS['uri']) > 1) { // Don't redirect the root URI (/)
				$GLOBALS['smarty']->assign('authentication_redirectUri', urlencode($GLOBALS['uri']));
			}
			$GLOBALS['section'] = 'default';
			$GLOBALS['subSection'] = 'authentication';
			$GLOBALS['user'] = null;
			$_SESSION['userId'] = null;
			
			$this->authentication();
			exit;
		}
	}
	
	
	/* +----------------------------------------------------------------+ *\
	|* | TESTS                                                          | *|
	\* +----------------------------------------------------------------+ */

	public function index()
	{
		$this->dashboard();
	}

	public function tests_phpinfo()
	{
		echo phpinfo();
		exit;
	}

	public function tests_showUser()
	{
		$user = User::getByUsername('matamouros');
		var_dump($user);
		exit;
	}

	public function tests_tasks()
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

		$rr = new Build_BuilderElement_Task_ReplaceRegexp();
		$rr->setFile('/tmp/whatevs.txt');
		//$rr->setFilesets(array($fileset));
		$rr->setFlags('gmi');
		$rr->setReplace('asd$1');
		$rr->setMatch('/^ola"asd(.*)$/');

		$target = new Build_BuilderElement_Target();
		$target->setName('tests');
		$target->setTasks(array($rr));
		//echo $target->toString('php');

		$target2 = new Build_BuilderElement_Target();
		$target2->setName('tests2');
		//$target->setTasks(array($delete, $exec));
		$target2->setTasks(array($echo, $rr));
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

	public function tests_pchart1()
	{
		$projectUser = Project_User::getByUser($GLOBALS['project'], $GLOBALS['user']);
		$n = new NotificationSettings();
		$projectUser->setNotifications($n);
		var_dump($projectUser);
		exit;
	}

	public function tests_pchart2()
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
			$assertions = 0;	// total number of "test" points
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

	public function tests_pchart3()
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

	public function tests_system()
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


	public function admin()
	{
		$this->_render();
	}

	public function asset()
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
				$extension	 = strtolower(substr($filename, $pos+1));
				$ext['jpg']	= 'image/jpeg';
				$ext['jpeg'] = 'image/jpeg';
				$ext['gif']	= 'image/gif';
				$ext['png']	= 'image/png';
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
				$filepath = $build->getBuildDir() . CINTIENT_CODECOVERAGE_HTML_DIR . $_GET['f'];
				$filename = $_GET['f'];
			} elseif (!empty($_GET['r'])) { // Release file
				$filepath = ''; // will always return false on file_exists() check
				if ($build->getReleaseFile() != '') {
					$filepath = $GLOBALS['project']->getReleasesDir() . $build->getReleaseFile();
					$filename = $build->getReleaseFile();
				}
			} else { // Normal file
				$filepath = $build->getBuildDir() . $_GET['f'];
				$filename = $_GET['f'];
			}
			if (!file_exists($filepath)) {
				SystemEvent::raise(SystemEvent::INFO, "Requested asset not found. [PID={$GLOBALS['project']->getId()}] [BID={$build->getId()}] [FILE={$filepath}]", __METHOD__);
				// TODO: Add an error message to the user in this redirect
				Redirector::redirectToUri(UrlManager::getForDashboard());
				exit;
			}
			$pos = strrpos($filepath, '.');
			if ($pos) {
				$extension	 = strtolower(substr($filepath, $pos+1));
				$ext['jpg']	= 'image/jpeg';
				$ext['jpeg'] = 'image/jpeg';
				$ext['gif']	= 'image/gif';
				$ext['png']	= 'image/png';
				$ext['swf']	= 'application/x-shockwave-flash';
				$ext['css']	= 'text/css';
				$ext['xml']	= 'text/xml';
				$ext['svg']	= 'image/svg+xml';
				$ext['gz']	 = 'application/x-gzip'; // Extension should be tar.gz but I don't want to change the strrpos() above
				$ext['tgz']	= 'application/x-gzip'; // @see http://www.yolinux.com/TUTORIALS/LinuxTutorialMimeTypesAndApplications.html
				$ext['zip']	= 'application/zip';		// @see http://www.yolinux.com/TUTORIALS/LinuxTutorialMimeTypesAndApplications.html
				if (isset($ext[$extension])) {
					header('Content-Type: '.$ext[$extension]);
				}
			}
			header('Last-Modified: '.date('r',filectime($filepath)));
			header('Content-Length: '.filesize($filepath));
			if ($extension == 'gz' || $extension == 'tgz' || $extension == 'zip') {
				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename={$filename}");
				header("Content-Transfer-Encoding: binary");
			}
			readfile($filepath);
		}
		exit;
	}

	public function authentication()
	{
		if (isset($GLOBALS['user']) && $GLOBALS['user'] instanceof User) {
			header("Location: " . UrlManager::getForDashboard());
			exit;
		}
		$this->_render();
	}

	/**
	 * Shows a list of available projects to the current user, for selection. Any
	 * project for which the user has at least read access level.
	 */
	public function dashboard()
	{
		//
		// Small "hack" to always have a project in GLOBALS, even before
		// one is actively clicked and visited. This allows a default project
		// (tipically the first one) to already be selected in the dashboard
		// and the respective project menu item filled.
		//
		// This does take into account that if $GLOBALS['project'] gets here
		// empty, it's because the webHandler couldn't populate it.
		//
		// This probably isn't the best thing to do, since we're initializing
		// variables here that will be used throughout the system. If anything
		// changes on the webHandler, don't forget to reflect that here also.
		//
		$projects = Project::getList($GLOBALS['user'], Access::READ);
		if (!($GLOBALS['project'] instanceof Project) && !empty($projects) && $projects[0] instanceof Project) {
			$GLOBALS['project'] = $projects[0];
			$_SESSION['projectId'] = $projects[0]->getId();
		}
		$GLOBALS['smarty']->assign('dashboard_projectList', $projects);
		$this->_render();
	}

	public function install()
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

	public function logout()
	{
		Auth::logout();
		header('Location: ' . CINTIENT_BASE_URL . '/');
		exit;
	}

	public function notFound()
	{}

	public function notAuthorized()
	{}

	public function project()
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
		
		$this->_render();
	}

	public function project_edit()
	{
		if (!isset($GLOBALS['project']) || !($GLOBALS['project'] instanceof Project)) {
			SystemEvent::raise(SystemEvent::ERROR, "Problems fetching requested project.", __METHOD__);
			Redirector::redirectToUri(UrlManager::getForDashboard());
			exit;
		}
		$GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
		self::providerAvailableBuilderElements();
		$this->_render();
	}

	public function project_history()
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
					if (is_array($viewData)) {
						foreach($viewData as $key => $value) {
							$GLOBALS['smarty']->assign($key, $value);
						}
					}
					$o = null;
					unset($o);
				}
			}
		}

		// Last assignments
		$GLOBALS['smarty']->assign('project_buildList', Project_Build::getList($GLOBALS['project'], $GLOBALS['user']));
		$GLOBALS['smarty']->assign('project_build', $build);
		$GLOBALS['smarty']->assign('project', $GLOBALS['project']);
		
		$this->_render();
	}

	public function project_new()
	{
		$GLOBALS['smarty']->assign('project_availableConnectors', ScmConnector::getAvailableConnectors());
		
		$this->_render();
	}

	/**
	 * Provider method for getting all builder elements nicely fit on an
	 * array. Originally intended for the builders setup of project edit.
	 */
	public function providerAvailableBuilderElements()
	{
		$dir = CINTIENT_INSTALL_DIR . 'src/core/Build/BuilderElement/';
		$dir = str_replace(array('\\', '//'), '/', $dir);
		
		$elements = array();
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		//
		// TODO: Disabling support for Target and Project at the moment, for simplification purposes.
		//
		$exceptions = array('**/Target.php', '**/Project.php', '**/Fileset.php');
		foreach (new Framework_FilesystemFilterIterator($it, $dir, array('**/*'), $exceptions) as $entry) {
			$levels = explode('/', str_replace(array('\\', '//'), '/', substr($entry, strlen($dir))));
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
		// It makes sure that all tasks/subtasks are properly sorted, across
		// all operating systems - while it was properly sorted in Mac OS,
		// it was not so in a Linux flavour.
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
	public function providerInstallationStats()
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

	public function registration()
	{
		$this->_render();
	}

	public function settings()
	{
		$this->_render();
	}
}