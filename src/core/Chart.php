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

include_once 'lib/pChart/class/pData.class';
include_once 'lib/pChart/class/pDraw.class';
include_once 'lib/pChart/class/pImage.class';

/**
 * @package Chart
 */
class Chart
{
  static public function unitTests($filename, Array $yAxis, Array $oks, Array $fails)
  {
    $chartWidth = CHART_JUNIT_DEFAULT_WIDTH;
    $chartHeight = 25 * count($yAxis) + 60;

    $MyData = new pData();
    $MyData->addPoints($oks, 'Ok');
    $MyData->addPoints($fails, 'Failed');
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
    $MyData->addPoints($yAxis,' ');
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
    $myPicture->render($filename);
    if (!file_exists($filename)) {
      return false;
    } else {
      return true;
    }
  }
}
