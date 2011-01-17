<?php   
 /* @ 700x230 Splitted path chart drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pSplit.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(700,230);
 $myPicture->drawGradientArea(0,0,500,230,DIRECTION_HORIZONTAL,array("StartR"=>220,"StartG"=>220,"StartB"=>220,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
 $RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>100);
 $myPicture->drawFilledRectangle(500,0,700,230,$RectangleSettings);
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"pSplit - Draw splitted path charts",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints(array(30,20,15,10,8,4),"Score"); 
 $MyData->addPoints(array("End of visit","Home Page","Product Page","Sales","Statistics","Prints"),"Labels");
 $MyData->setAbscissa("Labels");

 /* Create the pSplit object */
 $SplitChart = new pSplit();

 /* Draw the split chart */
 $Settings = array("TextPos"=>TEXT_POS_RIGHT,"TextPadding"=>10,"Spacing"=>20,"Surrounding"=>40);
 $myPicture->setGraphArea(10,20,340,230);
 $SplitChart->drawSplitPath($myPicture,$MyData,$Settings);

 /* Create and populate the pData object */
 $MyData2 = new pData();   
 $MyData2->addPoints(array(30,20,15),"Score"); 
 $MyData2->addPoints(array("UK","FR","ES"),"Labels");
 $MyData2->setAbscissa("Labels");

 /* Draw the split chart */
 $Settings = array("TextPadding"=>4,"Spacing"=>30,"Surrounding"=>20);
 $myPicture->setGraphArea(350,50,690,200);
 $SplitChart->drawSplitPath($myPicture,$MyData2,$Settings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.split.png");
?>