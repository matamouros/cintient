<?php   
 /* @ 700x230 Playing with shadows drawing example. */

 /* pChart library inclusions */
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(700,230);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"setShadow() - Add shadows",array("R"=>255,"G"=>255,"B"=>255));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw a filled circle */ 
 $formSettings = array("R"=>201,"G"=>230,"B"=>40,"Alpha"=>100,"Surrounding"=>30);
 $myPicture->drawFilledCircle(90,120,30,$formSettings);

 /* Draw a filled rectangle */ 
 $formSettings = array("R"=>231,"G"=>197,"B"=>40,"Alpha"=>100,"Surrounding"=>30);
 $myPicture->drawFilledRectangle(160,90,280,150,$formSettings);

 /* Draw a filled rounded rectangle */ 
 $formSettings = array("R"=>231,"G"=>102,"B"=>40,"Alpha"=>100,"Surrounding"=>70);
 $myPicture->drawRoundedFilledRectangle(320,90,440,150,5,$formSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.setShadow.png");
?>