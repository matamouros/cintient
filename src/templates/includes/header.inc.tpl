{*
    Cintient, Continuous Integration made simple.
    Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca

    This file is part of Cintient.

    Cintient is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Cintient is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Cintient. If not, see <http://www.gnu.org/licenses/>.

*}<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta property="url" content="{*SERVER_NAME*}">
  <title>Cintient</title>
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/font_anonymouspro.css" />
  <link rel="stylesheet" href="css/font_orbitron.css" />
  <link rel="stylesheet" href="css/font_syncopate.css" />
  <link rel="stylesheet" href="css/lib/avataruploader.css" />
  <link rel="stylesheet" href="js/lib/jgrowl/jquery.jgrowl.css" />
  <link rel="stylesheet" href="js/lib/tipTipv13/tipTip.css" />
  {*<link rel="stylesheet" href="css/global.css" />*}
  <link rel="stylesheet" href="css/lib/bootstrap.css" />
  <link rel="stylesheet" href="css/new.css" />
{foreach $cssIncludes as $cssInclude}
  <link rel="stylesheet" href="{$cssInclude}" />
{/foreach}
  <link rel="icon" href="favicon.ico">
  <!--[if lt IE 9]>
  <script src="js/lib/html5.js"></script>
  <![endif]-->
  <link rel='index' title='Cintient' href='{*SERVER_NAME*}' />
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="js/lib/jquery-1.6.4.min.js"></script>
  <script type="text/javascript" src="js/lib/jquery-ui-1.8.12.custom.min.js"></script>
  <script type="text/javascript" src="js/lib/jgrowl/jquery.jgrowl_minimized.js"></script>
  <script type="text/javascript" src="js/lib/tipTipv13/jquery.tipTip.minified.js"></script>
  <script type="text/javascript" src="js/cintient.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-dropdown.js"></script>
{foreach $jsIncludes as $jsInclude}
  <script type="text/javascript" src="{$jsInclude}"></script>
{/foreach}
  <script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionHeader();
});
// ]]>
  </script>
</head>
<body>
{if $globals_user instanceof User}
  <div class="topbar">
    <div class="fill">
      <div class="container">
        <div id="topbarLeft">
          <div id="logoLettering"><a href="{UrlManager::getForDashboard()}"><div id="cintientLettering" style="display: none;">Cintient</div></a></div>{* <img src="imgs/redhalo_45.jpg" height="25">*}
          <div>
            <ul class="nav">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle">{$subSectionTitle}</a>
                <ul class="dropdown-menu">
                  <li><a href="{UrlManager::getForDashboard()}">Dashboard</a></li>
                  <li><a href="{UrlManager::getForProjectNew()}">New project</a></li>
                  {if $globals_user->hasCos(UserCos::ROOT)}<li><a href="{UrlManager::getForDashboard()}">Admin</a></li>{/if}
                </ul>
              </li>
            </ul>
        		  {if !empty($menuLinks)}<ul class="sectionLinks">{$menuLinks}</ul>{/if}
          </div>
        </div>
        <div id="topbarRight">{* Bootstrap uses ul.secondary-nav to implement this right aligned nav... *}
          <div id="user">
            <div id="avatar"><img id="avatarImg" src="{$globals_user->getAvatarUrl()}" width="30" height="30"></div>
            <div id="username">{$globals_user->getUsername()}</div>
            <div id="links"><a href="{UrlManager::getForSettings()}">Settings</a> | <a href="{UrlManager::getForLogout()}">Logout</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
{elseif $globals_subSection == 'registration'}
  <div class="topbar">
    <div class="fill">
      <div class="container">
        <div id="topbarLeft">
          <ul class="nav">
            <li id="historyBack"><a href="{UrlManager::getForDashboard()}">&#8226;</a>&#8226;<span class="ghosted">&#8226;</span></li>
            <li id="sectionName">{$subSectionTitle}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
{else}
  <div id="splashHeader" class="container">
    <header>
      <hgroup>
        <h1>Cintient</h1>
        <img src="imgs/redhalo.jpg" width="195" height="130">
      </hgroup>
    </header>
  </div>
<script type="text/javascript">
// <![CDATA[
$('#splashHeader h1').hide();
$('#splashHeader img').hide();
$(document).ready(function() {
	$('#splashHeader h1').fadeIn(300);
  $('#splashHeader img').fadeIn(300);
});
// ]]>
</script>
{/if}
  <div class="container">
    <div class="content" id="{$subSectionId}">
      <div class="page-header">
        {if !empty($subSectionImg)}<div class="projectAvatar40x40"><img src="{$subSectionImg}" width="40" height="40"></div>{/if}
        <h1>{$subSectionTitle} <small>{$subSectionDescription}</small></h1>
      </div>