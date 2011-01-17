<?php   
 /* @ 700x230 Stacked bar chart with rounded areas drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints(array(-9,-9,-9,-10,-10,-11,-12,-14,-16,-17,-18,-18,-19,-19,-18,-15,-12,-10,-9),"Probe 3");
 $MyData->addPoints(array(-10,-11,-11,-12,-12,-13,-14,-15,-17,-19,-22,-24,-23,-23,-22,-20,-18,-16,-14),"Probe 4");
 $MyData->setAxisName(0,"Temperatures");
 $MyData->addPoints(array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22),"Time");
 $MyData->setSerieDescription("Time","Hour of the day");
 $MyData->setAbscissa("Time");
 $MyData->setXAxisUnit("h");

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));

 /* Set the default font properties */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/verdana.ttf","FontSize"=>8));

 /* Draw the scale */
 $myPicture->setGraphArea(60,30,650,190);
 $myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10,"Mode"=>SCALE_MODE_ADDALL));

 /* Turn on shadow computing */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $myPicture->drawStackedBarChart(array("Rounded"=>TRUE,"DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO,"DisplaySize"=>6,"BorderR"=>255,"BorderG"=>255,"BorderB"=>255));

 /* Draw a threshold */
 $myPicture->drawThreshold(0,array("R"=>0,"G"=>0,"B"=>0,"Ticks"=>4,"Wide"=>TRUE));

 /* Write the chart legend */
 $myPicture->drawLegend(570,215,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawStackedBarChart.rounded.png");
?>