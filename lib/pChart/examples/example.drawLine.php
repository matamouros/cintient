<?php   
 /* @ 700x230 Line drawing example. */

 /* pChart library inclusions */
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(700,230);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Draw the picture border */ 
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"drawLine() - Basis",array("R"=>255,"G"=>255,"B"=>255));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw some lines */ 
 for($i=1;$i<=100;$i=$i+4)
  $myPicture->drawLine($i+5,215,$i*7+5,30,array("R"=>rand(0,255),"G"=>rand(0,255),"B"=>rand(0,255),"Ticks"=>rand(0,4)));

 /* Draw an horizontal dashed line with extra weight */
 $myPicture->drawLine(370,160,650,160,array("R"=>0,"G"=>0,"B"=>0,"Ticks"=>4,"Weight"=>3));

 /* Another example of extra weight */
 $myPicture->drawLine(370,180,650,200,array("R"=>255,"G"=>255,"B"=>255,"Ticks"=>15,"Weight"=>1));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawLine.png");
?>