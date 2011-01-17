<?php   
 /* @ 600x600 Spring chart : complex network drawing example. */

 /* pChart library inclusions */
 include("../class/pData.class");
 include("../class/pDraw.class");
 include("../class/pSpring.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(600,600);
 $myPicture->drawGradientArea(0,0,600,600,DIRECTION_HORIZONTAL,array("StartR"=>217,"StartG"=>250,"StartB"=>116,"EndR"=>181,"EndG"=>209,"EndB"=>27,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,600,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,599,599,array("R"=>0,"G"=>0,"B"=>0));

 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"pSpring - Draw spring charts",array("R"=>255,"G"=>255,"B"=>255));

 /* Set the graph area boundaries*/ 
 $myPicture->setGraphArea(20,20,580,580);

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>9,"R"=>80,"G"=>80,"B"=>80));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pSpring object */ 
 $SpringChart = new pSpring();

 /* Set the default parameters for newly added nodes */ 
 $SpringChart->setNodeDefaults(array("FreeZone"=>70));

 /* Create 11 random nodes */ 
 for($i=0;$i<=10;$i++)
  {
   $Connections = ""; $RdCx = rand(0,1);
   for($j=0;$j<=$RdCx;$j++)
    {
     $RandCx = rand(0,10);
     if ( $RandCx != $j )
      { $Connections[] = $RandCx; }
    }

   $SpringChart->addNode($i,array("Name"=>"Node ".$i,"Connections"=>$Connections));
  }

 /* Draw the spring chart */ 
 $Result = $SpringChart->drawSpring($myPicture,array("DrawQuietZone"=>TRUE,"Algorithm"=>ALGORITHM_CIRCULAR,"RingSize"=>100)); //WEIGHTED

 /* Output the statistics */ 
 // print_r($Result);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.spring.complex.png");
?>