<?php   
 /* @ 700x230 Drawing 3D Pie charts. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pPie.class");
 include("../class/pImage.class");

 /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints(array(40,30,20),"ScoreA");  
 $MyData->setSerieDescription("ScoreA","Application A");

 /* Define the absissa serie */
 $MyData->addPoints(array("A","B","C"),"Labels");
 $MyData->setAbscissa("Labels");

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);
 $myPicture->drawGradientArea(0,0,500,230,DIRECTION_HORIZONTAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
 $RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>100);
 $myPicture->drawFilledRectangle(500,0,700,230,$RectangleSettings);
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"pPie - Draw 3D pie charts",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

 /* Create the pPie object */ 
 $PieChart = new pPie($myPicture,$MyData);

 /* Define the slice color */
 $PieChart->setSliceColor(0,array("R"=>143,"G"=>197,"B"=>0));
 $PieChart->setSliceColor(1,array("R"=>97,"G"=>77,"B"=>63));
 $PieChart->setSliceColor(2,array("R"=>97,"G"=>113,"B"=>63));

 /* Draw a simple pie chart */ 
 $PieChart->draw3DPie(120,125,array("SecondPass"=>FALSE));

 /* Draw an AA pie chart */ 
 $PieChart->draw3DPie(340,125,array("DrawLabels"=>TRUE,"Border"=>TRUE));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw a splitted pie chart */ 
 $PieChart->draw3DPie(560,125,array("DataGapAngle"=>10,"DataGapRadius"=>6,"Border"=>TRUE));

 /* Write the legend */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/MankSans.ttf","FontSize"=>11));
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
 $myPicture->drawText(140,200,"Single AA pass",array("R"=>0,"G"=>0,"B"=>0,"Align"=>TEXT_ALIGN_TOPMIDDLE));
 $myPicture->drawText(440,200,"Extended AA pass / Splitted",array("R"=>0,"G"=>0,"B"=>0,"Align"=>TEXT_ALIGN_TOPMIDDLE));

 /* Write the legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6,"R"=>255,"G"=>255,"B"=>255));
 $PieChart->drawPieLegend(600,8,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.draw3DPie.png");
?>