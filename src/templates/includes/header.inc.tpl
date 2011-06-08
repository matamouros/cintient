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
  <link rel="stylesheet" href="/css/font_anonymouspro.css" />
  <link rel="stylesheet" href="/css/font_orbitron.css" />
  <link rel="stylesheet" href="/css/font_syncopate.css" />
  <link rel="stylesheet" href="/css/global.css" />
  <link rel="stylesheet" href="/css/avataruploader.css" />
  <link rel="icon" href="/favicon.ico">
  <!--[if lt IE 9]>
  <script src="/js/html5.js"></script>
  <![endif]-->
  <link rel='index' title='Cintient' href='{*SERVER_NAME*}' />
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="/js/jquery-1.6.js"></script>
  <script type="text/javascript" src="/js/jquery-ui-1.8.12.custom.min.js"></script>
</head>
<body>
  <div id="header" class="containerTopLevel">
{if $globals_user instanceof User}
    <div id="userHeader" class="container">
      <header>
        <hgroup>
          <h1 id="logo">Cintient <img src="/imgs/redhalo_45.jpg" height="25"></h1>
          <nav>
            <div id="user">
              <div id="avatar"><img id="avatarImg" src="{$globals_user->getAvatarUrl()}" width="40" height="40"></div>
              <div id="username">{$globals_user->getUsername()}</div>
              <div id="links">{if $globals_user->hasCos(UserCos::ROOT)}<a href="{UrlManager::getForDashboard()}">admin</a> | {/if}<a href="/settings/">settings</a> | <a href="/logout/">logout</a></div>
            </div>
          </nav>
        </hgroup>
      </header>
    </div>
  </div>
  <div id="menu" class="containerTopLevel">
    <nav id="mainMenu">
      <ul>
        <li id="historyBack">{if !empty($backLink)}<a href="{UrlManager::getForDashboard()}">&#8226;</a><a href="{$backLink}">&#8226;</a>&#8226;{elseif $globals_subSection == 'dashboard'}&#8226;<span class="ghosted">&#8226;&#8226;</span>{else}<a href="{UrlManager::getForDashboard()}">&#8226;</a>&#8226;<span class="ghosted">&#8226;</span>{/if}</li>
        <li id="sectionName">{$subSectionTitle}</li>
        {if !empty($menuLinks)}<li class="sectionTopOptions">{$menuLinks}</li>{/if}
      </ul>
    </nav>
<script type="text/javascript">
// <![CDATA[
$('#logo').hide();
$(document).ready(function() {
  $('#logo').show(200);
});
// ]]>
</script>
{elseif $globals_subSection == 'registration'}
    <div id="userHeader" class="container">
      <header>
        <hgroup>
          <h1 id="logo">Cintient <img src="/imgs/redhalo_45.jpg" height="25"></h1>
        </hgroup>
      </header>
    </div>
  </div>
  <div id="menu" class="containerTopLevel">
    <nav id="mainMenu">
      <ul>
        <li id="historyBack"><a href="{UrlManager::getForDashboard()}">&#8226;</a>&#8226;<span class="ghosted">&#8226;</span></li>
        <li id="sectionName">{$subSectionTitle}</li>
      </ul>
    </nav>
<script type="text/javascript">
// <![CDATA[
$('#logo').hide();
$(document).ready(function() {
  $('#logo').show(200);
});
// ]]>
</script>
{else}
    <div id="splashHeader" class="container">
      <header>
        <hgroup>
          <h1>Cintient</h1>
          <img src="/imgs/redhalo.jpg" width="195" height="130">
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
  </div>
  <div id="main" class="containerTopLevel">