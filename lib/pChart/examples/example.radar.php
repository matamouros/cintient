<?php   
 /* @ 700x230 Radar bar chart drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pRadar.class");
 include("../class/pImage.class");

 /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints(array(40,20,15,10,8,4),"ScoreA");  
 $MyData->addPoints(array(8,10,12,20,30,15),"ScoreB"); 
 $MyData->setSerieDescription("ScoreA","Application A");
 $MyData->setSerieDescription("ScoreB","Application B");

 /* Define the absissa serie */
 $MyData->addPoints(array("Size","Speed","Reliability","Functionalities","Ease of use","Weight"),"Labels");
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
 $myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 $SplitChart = new pRadar();

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(10,25,340,225);
 $Options = array("Layout"=>RADAR_LAYOUT_STAR,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>100,"EndR"=>207,"EndG"=>227,"EndB"=>125,"EndAlpha"=>50));
 $SplitChart->drawRadar($myPicture,$MyData,$Options);

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(350,25,690,225);
 $Options = array("Layout"=>RADAR_LAYOUT_CIRCLE,"LabelPos"=>RADAR_LABELS_HORIZONTAL,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>50,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>30));
 $SplitChart->drawRadar($myPicture,$MyData,$Options);

 /* Write the chart legend */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $myPicture->drawLegend(270,205,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.radar.png");
?>