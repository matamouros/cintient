<?php   
 /* @ 700x230 Pie chart legend drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pPie.class");
 include("../class/pImage.class");

 /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints(array(40,60,15,10,6,4),"ScoreA");  
 $MyData->setSerieDescription("ScoreA","Application A");

 /* Define the absissa serie */
 $MyData->addPoints(array("<10","10<>20","20<>40","40<>60","60<>80",">80"),"Labels");
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
 $myPicture->drawText(10,13,"drawPieLegend - Draw pie charts legend",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>150,"G"=>150,"B"=>150,"Alpha"=>100));

 /* Create the pPie object */ 
 $PieChart = new pPie($myPicture,$MyData);

 /* Draw two AA pie chart */ 
 $PieChart->draw2DPie(200,100,array("Border"=>TRUE));
 $PieChart->draw2DPie(440,115,array("Border"=>TRUE));

 /* Write down the legend next to the 2nd chart*/
 $PieChart->drawPieLegend(550,70);

 /* Write a legend box under the 1st chart */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $PieChart->drawPieLegend(90,176,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

 /* Write the bottom legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawGradientArea(1,200,698,228,DIRECTION_VERTICAL,array("StartR"=>247,"StartG"=>247,"StartB"=>247,"EndR"=>217,"EndG"=>217,"EndB"=>217,"Alpha"=>100));
 $myPicture->drawLine(1,199,698,199,array("R"=>100,"G"=>100,"B"=>100));
 $myPicture->drawLine(1,200,698,200,array("R"=>255,"G"=>255,"B"=>255));
 $PieChart->drawPieLegend(10,210,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawPieLegend.png");
?>