<?php   
 /* @ 700x230 Filled circle drawing example. */

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
 $myPicture->drawText(10,13,"drawCircle() - Transparency & colors",array("R"=>255,"G"=>255,"B"=>255));

 /* Draw some filled circles */ 
 $myPicture->drawFilledCircle(100,125,50,array("R"=>213,"G"=>226,"B"=>0,"Alpha"=>100));
 $myPicture->drawFilledCircle(140,125,50,array("R"=>213,"G"=>226,"B"=>0,"Alpha"=>70));
 $myPicture->drawFilledCircle(180,125,50,array("R"=>213,"G"=>226,"B"=>0,"Alpha"=>40));
 $myPicture->drawFilledCircle(220,125,50,array("R"=>213,"G"=>226,"B"=>0,"Alpha"=>20));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw a customized filled circles */ 
 $CircleSettings = array("R"=>209,"G"=>31,"B"=>27,"Alpha"=>100,"Surrounding"=>30);
 $myPicture->drawFilledCircle(480,60,19,$CircleSettings);

 /* Draw a customized filled circles */ 
 $CircleSettings = array("R"=>209,"G"=>125,"B"=>27,"Alpha"=>100,"Surrounding"=>30);
 $myPicture->drawFilledCircle(480,100,19,$CircleSettings);

 /* Draw a customized filled circles */ 
 $CircleSettings = array("R"=>209,"G"=>198,"B"=>27,"Alpha"=>100,"Surrounding"=>30,"Ticks"=>4);
 $myPicture->drawFilledCircle(480,140,19,$CircleSettings);

 /* Draw a customized filled circles */ 
 $CircleSettings = array("R"=>134,"G"=>209,"B"=>27,"Alpha"=>100,"Surrounding"=>30,"Ticks"=>4);
 $myPicture->drawFilledCircle(480,180,19,$CircleSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawFilledCircle.png");
?>