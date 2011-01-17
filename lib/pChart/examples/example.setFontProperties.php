<?php   
 /* @ 700x230 Playing with fonts drawing example. */

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
 $myPicture->drawText(10,13,"setFontProperties() - set default font properties",array("R"=>255,"G"=>255,"B"=>255));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/advent_light.ttf","FontSize"=>20));
 $myPicture->drawText(60,115,"10 degree text",array("Angle"=>10));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/calibri.ttf","FontSize"=>20));
 $myPicture->drawText(75,130,"10 degree text",array("Angle"=>10));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/GeosansLight.ttf","FontSize"=>20));
 $myPicture->drawText(90,145,"10 degree text",array("Angle"=>10));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/MankSans.ttf","FontSize"=>20));
 $myPicture->drawText(105,160,"10 degree text",array("Angle"=>10));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/MankSans.ttf","FontSize"=>30,"R"=>231,"G"=>50,"B"=>36));
 $myPicture->drawText(340,90,"Some big red text");

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6,"R"=>29,"G"=>70,"B"=>111));
 $myPicture->drawText(340,100,"Some blue text");

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.setFontProperties.png");
?>