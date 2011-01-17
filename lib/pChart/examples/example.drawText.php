<?php   
 /* @ 700x230 Text drawing example. */

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
 $myPicture->drawText(10,13,"drawText() - add some text to your charts",array("R"=>255,"G"=>255,"B"=>255));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/advent_light.ttf","FontSize"=>20));
 $TextSettings = array("R"=>255,"G"=>255,"B"=>255,"Angle"=>10);
 $myPicture->drawText(60,115,"10 degree text",$TextSettings);

 /* Write some text */ 
 $TextSettings = array("R"=>0,"G"=>0,"B"=>0,"Angle"=>0,"FontSize"=>40);
 $myPicture->drawText(220,130,"Simple text",$TextSettings);

 /* Write some text */ 
 $TextSettings = array("R"=>200,"G"=>100,"B"=>0,"Angle"=>90,"FontSize"=>14);
 $myPicture->drawText(500,170,"Vertical Text",$TextSettings);

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Bedizen.ttf","FontSize"=>6));
 $TextSettings = array("DrawBox"=>TRUE,"BoxRounded"=>TRUE,"R"=>0,"G"=>0,"B"=>0,"Angle"=>0,"FontSize"=>10);
 $myPicture->drawText(220,160,"Encapsulated text",$TextSettings);

 /* Write some text */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>6));
 $TextSettings = array("DrawBox"=>TRUE,"R"=>0,"G"=>0,"B"=>0,"Angle"=>0,"FontSize"=>10);
 $myPicture->drawText(220,195,"Text in a box",$TextSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawText.png");
?>