<?php   
 /* @ 700x230 This example shows how to tune the scale by skipping labels. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create the pData object with some random values*/
 $MyData = new pData();  
 $MyData->addRandomValues("Probe 1",array("Values"=>30,"Min"=>0,"Max"=>4));
 $MyData->addRandomValues("Probe 2",array("Values"=>30,"Min"=>6,"Max"=>10));
 $MyData->addRandomValues("Probe 3",array("Values"=>30,"Min"=>12,"Max"=>16));
 $MyData->addRandomValues("Probe 4",array("Values"=>30,"Min"=>18,"Max"=>22));
 $MyData->setAxisName(0,"Probes");

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"addRandomValues() :: assess your scales",array("R"=>255,"G"=>255,"B"=>255));

 /* Draw the scale */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>11));
 $myPicture->setGraphArea(50,60,670,190);
 $myPicture->drawFilledRectangle(50,60,670,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
 $myPicture->drawScale(array("LabelSkip"=>3,"DrawSubTicks"=>TRUE));

 /* Graph title */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->drawText(50,52,"Magnetic noise",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

 /* Draw the data series */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $myPicture->drawSplineChart();
 $myPicture->setShadow(FALSE);

 /* Write the legend */
 $myPicture->drawLegend(475,50,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.addRandomValues.png");
?>