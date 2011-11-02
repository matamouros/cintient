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
  <link rel="stylesheet" href="js/lib/tipTipv13/tipTip.css" />
  <link rel="stylesheet" href="css/lib/bootstrap.css" />
{foreach $cssIncludes as $cssInclude}
  <link rel="stylesheet" href="{$cssInclude}" />
{/foreach}
  <link rel="stylesheet" href="css/new.css" />
  <link rel="icon" href="favicon.ico">
  <!--[if lt IE 9]>
  <script src="js/lib/html5.js"></script>
  <![endif]-->
  <link rel='index' title='Cintient' href='{*SERVER_NAME*}' />
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="js/lib/jquery-1.6.4.min.js"></script>
  <script type="text/javascript" src="js/lib/tipTipv13/jquery.tipTip.minified.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-alerts.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-dropdown.js"></script>
{foreach $jsIncludes as $jsInclude}
  <script type="text/javascript" src="{$jsInclude}"></script>
{/foreach}
  <script type="text/javascript" src="js/cintient.js"></script>
  <script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionHeader();
});
// ]]>
  </script>
</head>
<body>
  <div class="topbar">
    <div class="fill">
      <div class="container">
        <div id="topbarLeft">
          <div id="logoLettering">{if $globals_user instanceof User}<a href="{UrlManager::getForDashboard()}">{/if}<div id="cintientLettering" style="display: none;">Cintient</div>{if $globals_user instanceof User}</a>{/if}</div>
{if $globals_user instanceof User}
          <div class="menuLinks">
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
{/if}
        </div>
      </div>
    </div>
  </div>
  <div id="alertPane"></div>
  <div class="container">
    <div class="mainContent" id="{$subSectionId}">
      <div class="page-header">
        {if !empty($subSectionImg)}<div class="projectAvatar40x40"><img src="{$subSectionImg}" width="40" height="40"></div>{/if}
        <h1>{$subSectionTitle} <small>{$subSectionDescription}</small></h1>
      </div>