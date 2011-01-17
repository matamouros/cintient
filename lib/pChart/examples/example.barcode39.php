<?php   
 /* @ 700x230 Barcode 39 drawing example. */

 /* pChart library inclusions */
 include("../class/pDraw.class");
 include("../class/pBarcode39.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(700,230);

 /* Create the backgound */
 $myPicture->drawGradientArea(0,0,500,230,DIRECTION_HORIZONTAL,array("StartR"=>217,"StartG"=>250,"StartB"=>116,"EndR"=>181,"EndG"=>209,"EndB"=>27,"Alpha"=>100));
 $RectangleSettings = array("R"=>181,"G"=>209,"B"=>27,"Alpha"=>100);
 $myPicture->drawFilledRectangle(500,0,700,230,$RectangleSettings);

 /* Draw the top bar */
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"Barcode 39 - Add barcode to your pictures",array("R"=>255,"G"=>255,"B"=>255));

 /* Create the barcode 39 object */
 $Barcode = new pBarcode39("../");

 /* Draw a simple barcode */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
 $Settings = array("ShowLegend"=>TRUE,"DrawArea"=>TRUE);
 $Barcode->draw($myPicture,"pChart Rocks!",50,50,$Settings);

 /* Draw a rotated barcode */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>12));
 $Settings = array("ShowLegend"=>TRUE,"DrawArea"=>TRUE,"Angle"=>90);
 $Barcode->draw($myPicture,"Turn me on",650,50,$Settings);

 /* Draw a rotated barcode */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>12));
 $Settings = array("R"=>255,"G"=>255,"B"=>255,"AreaR"=>150,"AreaG"=>30,"AreaB"=>27,"ShowLegend"=>TRUE,"DrawArea"=>TRUE,"Angle"=>350,"AreaBorderR"=>70,"AreaBorderG"=>20,"AreaBorderB"=>20);
 $Barcode->draw($myPicture,"Do what you want !",290,140,$Settings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.barcode39.png");
?>