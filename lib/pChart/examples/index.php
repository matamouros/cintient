<?php if ( isset($_GET["Action"])) { $Script = $_GET["Script"]; highlight_file($Script); exit(); } ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
 <title>pChart 2.x - examples rendering</title>
 <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
 <style>
  body { background-color: #F0F0F0; font-family: tahoma; font-size: 14px; height: 100%; overflow: auto;}
  td   { font-family: tahoma; font-size: 11px; }
  a.smallLinkGrey:link    { text-decoration: none; color: #6A6A6A; }
  a.smallLinkGrey:visited { text-decoration: none; color: #6A6A6A; }
  a.smallLinkGrey:hover   { text-decoration: underline; color: #6A6A6A; }
  a.smallLinkBlack:link    { text-decoration: none; color: #000000; }
  a.smallLinkBlack:visited { text-decoration: none; color: #000000; }
  a.smallLinkBlack:hover   { text-decoration: underline; color: #000000; }
 </style>
</head>
<body>

<table style='padding: 1px; background-color: #E0E0E0; border: 1px solid #D0D0D0;'><tr>
 <td><img src='resources/application_view_tile.png' width=16 height=16 alt=''/></td>
 <td><b>Examples</b></td>
 <td width=15></td>
 <td><img src='resources/application_view_list.png' width=16 height=16 alt=''/></td>
 <td><a class=smallLinkGrey href='sandbox/'>Sandbox</a></td>
 <td width=15></td>
 <td><img src='resources/application_view_list.png' width=16 height=16 alt=''/></td>
 <td><a class=smallLinkGrey href='delayedLoader/'>Delayed loader</a></td>
</tr></table>

<div style='margin-top: 4px; border: 1px solid #D0D0D0; background-color: #FAFAFA; height: 200px; overflow: auto'>
<table>
<?php
 $Exclusion = array(".","..","index.php","buildAll.cmd","pictures","resources","delayedLoader","sandbox");

 $DirectoryHandle = opendir(".");
  {
   while (($FileName = readdir($DirectoryHandle)) !== false)
   {
    if ( !in_array($FileName,$Exclusion))
     {
      $FileHandle  = fopen($FileName, "r");
      $buffer      = fgets($FileHandle, 4096);
      $buffer      = fgets($FileHandle, 4096);
      fclose($FileHandle);

      $Description = str_replace("/* @","",$buffer);
      $Description = str_replace("*/","",$Description);
      $Values      = preg_split("/[\s]+/",$Description);

      if ( isset($Values[1]) ) { $Size = $Values[1]; } else { $Size = "?"; }

      $Description = right($Description,strlen($Description)-strlen($Size)-2);

      $FileShortName = str_replace("example.","",$FileName);
      $FileShortName = str_replace(".php","",$FileShortName);
      $FileShortName = trim($FileShortName);

      $FileSize = filesize($FileName);

      if ( $Size != "!" )
       {
        echo " <tr>\r\n";
        echo "  <td><img src='resources/chart_bar.png' width=16 height=16 alt=''/></td>\r\n";
        echo "  <td><a class=smallLinkGrey href='#' onclick='render(".chr(34).$FileName.chr(34).");'>".$FileShortName."</a></td>\r\n";
        echo "  <td>&nbsp;&nbsp;[ <a class=smallLinkBlack href='#' onclick='view(".chr(34).$FileName.chr(34).");'>view source</a> ]&nbsp;&nbsp;</td>\r\n";
        echo "  <td>&nbsp;&nbsp;".$Size."&nbsp;&nbsp;</td>\r\n";
        echo "  <td>&nbsp;&nbsp;".size($FileSize)."&nbsp;&nbsp;</td>\r\n";
        echo "  <td style='color: #439F5F;'><i>".$Description."</i></td>\r\n";
        echo " </tr>";
       }
     }
   }
  closedir($DirectoryHandle);
 }
?>
</table>
</div>

<br/>

<u>Rendering area</u>

<br/><br/>

<div style='display:table-cell; padding: 10px; border: 1px dashed #C0C0C0; vertical-align: middle; overflow: auto; background-image: url("resources/dash.png");'>
<div style='font-size: 10px;' id=render>
 <table><tr><td><img src='resources/accept.png' width=16 height=16 alt=""/></td><td>Click on an example to render it!</td></tr></table>
</div>
</div>

</body>
<script>
 URL = "";

 function render(PictureName)
  {
   opacity("render",100,0,100);

   RandomKey = Math.random(100);
   URL       = PictureName + "?Seed=" + RandomKey;

   ajaxRender(URL);
  }

 function StartFade()
  {
   Loader     = new Image();   
   Loader.src = URL;   
   setTimeout("CheckLoadingStatus()", 200);   
  }

 function CheckLoadingStatus()   
  {   
   if ( Loader.complete == true )   
    {
     changeOpac(0, "render");
     HTMLResult = "<center><img src='" + URL + "' alt=''/></center>";
     document.getElementById("render").innerHTML = HTMLResult;

     opacity("render",0,100,100);
    }
   else  
    setTimeout("CheckLoadingStatus()", 200);   
  }   

 function changeOpac(opacity, id)   
  {   
   var object = document.getElementById(id).style;   
   object.opacity = (opacity / 100);   
   object.MozOpacity = (opacity / 100);   
   object.KhtmlOpacity = (opacity / 100);   
   object.filter = "alpha(opacity=" + opacity + ")";   
  }   

 function wait()
  {
   HTMLResult = "<center><img src='resources/wait.gif' width=24 height=24 alt=''/><br>Rendering</center>";
   document.getElementById("render").innerHTML = HTMLResult;
   changeOpac(20, "render");
  }

 function opacity(id, opacStart, opacEnd, millisec)
  {
   var speed = Math.round(millisec / 100);
   var timer = 0;

   if(opacStart > opacEnd)
    {
     for(i = opacStart; i >= opacEnd; i--)
      {
       setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed));
       timer++;
      }
     setTimeout("wait()",(timer * speed));
    }
   else if(opacStart < opacEnd)
    {
     for(i = opacStart; i <= opacEnd; i++)
      {
       setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed));
       timer++;
      }
    }
  }

 function ajaxRender(URL)
  {
   var xmlhttp=false;   
   /*@cc_on @*/  
   /*@if (@_jscript_version >= 5)  
    try { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) { try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); } catch (E) { xmlhttp = false; } }  
   @end @*/  
  
   if (!xmlhttp && typeof XMLHttpRequest!='undefined')   
    { try { xmlhttp = new XMLHttpRequest(); } catch (e) { xmlhttp=false; } }   
  
   if (!xmlhttp && window.createRequest)   
    { try { xmlhttp = window.createRequest(); } catch (e) { xmlhttp=false; } }   
  
   xmlhttp.open("GET", URL,true);

   xmlhttp.onreadystatechange=function() { if (xmlhttp.readyState==4) { StartFade();  } }   
   xmlhttp.send(null)   
  }

 function view(URL)
  {
   var xmlhttp=false;   
   /*@cc_on @*/  
   /*@if (@_jscript_version >= 5)  
    try { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) { try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); } catch (E) { xmlhttp = false; } }  
   @end @*/  
  
   URL = "index.php?Action=View&Script=" + URL;

   if (!xmlhttp && typeof XMLHttpRequest!='undefined')   
    { try { xmlhttp = new XMLHttpRequest(); } catch (e) { xmlhttp=false; } }   
  
   if (!xmlhttp && window.createRequest)   
    { try { xmlhttp = window.createRequest(); } catch (e) { xmlhttp=false; } }   
  
   xmlhttp.open("GET", URL,true);

   xmlhttp.onreadystatechange=function() { if (xmlhttp.readyState==4) { Result = xmlhttp.responseText; document.getElementById("render").innerHTML = Result;  } }   
   xmlhttp.send(null)   
  }
</script>
</html>
<?php
 function size($Value)
  {
   if ( $Value < 1024 ) { return($Value." o."); }
   if ( $Value >= 1024 && $Value < 1024000 ) { return(floor($Value/1024)." ko."); }
   return(floor($Value/1024000))." mo.";
  }

 function left($value,$NbChar)  
  { return substr($value,0,$NbChar); }  
 
 function right($value,$NbChar)  
  { return substr($value,strlen($value)-$NbChar,$NbChar); }  
 
 function mid($value,$Depart,$NbChar)  
  { return substr($value,$Depart-1,$NbChar); }  
?>