<?php   
 /* @ 700x230 Chart legend drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints(array(24,25,26,25,25),"My Serie 1");
 $MyData->addPoints(array(80,85,84,81,82),"My Serie 2");
 $MyData->addPoints(array(17,16,18,18,15),"My Serie 3");
 $MyData->setSerieDescription("My Serie 1","Temperature");
 $MyData->setSerieDescription("My Serie 2","Humidity\n(in percentage)");
 $MyData->setSerieDescription("My Serie 3","Pressure");

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Draw the picture border */ 
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"drawLegend() - Write your chart legend",array("R"=>255,"G"=>255,"B"=>255));

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->drawLegend(70,60);

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/MankSans.ttf","FontSize"=>10,"R"=>30,"G"=>30,"B"=>30));
 $myPicture->drawLegend(230,60,array("BoxSize"=>4,"R"=>173,"G"=>163,"B"=>83,"Surrounding"=>20));

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>9,"R"=>80,"G"=>80,"B"=>80));
 $myPicture->drawLegend(400,60,array("Style"=>LEGEND_BOX,"BoxSize"=>4,"R"=>200,"G"=>200,"B"=>200,"Surrounding"=>20,"Alpha"=>30));

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawLegend(70,150,array("Mode"=>LEGEND_HORIZONTAL));

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $myPicture->drawLegend(400,150,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

 /* Write a legend box */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawFilledRectangle(1,200,698,228,array("Alpha"=>50,"R"=>255,"G"=>255,"B"=>255));
 $myPicture->drawLegend(10,208,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawLegend.png");
?>