<?php   
 /* @ 700x230 Spline drawing example. */

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
 $myPicture->drawText(10,13,"drawSpline() - for smooth line drawing",array("R"=>255,"G"=>255,"B"=>255));

 /* Enable shadow computing */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw a spline */
 $SplineSettings = array("R"=>255,"G"=>255,"B"=>255,"ShowControl"=>TRUE);
 $Coordinates = array(array(40,80),array(280,60),array(340,166),array(590,120));
 $myPicture->drawSpline($Coordinates,$SplineSettings);

 /* Draw a spline */
 $SplineSettings = array("R"=>255,"G"=>255,"B"=>255,"ShowControl"=>TRUE,"Ticks"=>4);
 $Coordinates = array(array(250,50),array(250,180),array(350,180),array(350,50));
 $myPicture->drawSpline($Coordinates,$SplineSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawSpline.png");
?>