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
  <script type="text/javascript" src="/js/jquery-1.4.4.js"></script>
</head>
<body>
  <div id="header" class="containerTopLevel">
{if $smarty.session.user instanceof User}
    <div id="userHeader" class="container">
      <header>
        <hgroup>
          <h1>Cintient <img src="/imgs/redhalo_45.jpg" height="25"></h1>
          <nav>
            <div id="user">
              <div id="avatar"><img id="avatarImg" src="{$smarty.session.user->getAvatarUrl()}" width="40" height="40"></div>
              <div id="username">{$smarty.session.user->getUsername()}</div>
              <div id="links"><a href="{URLManager::getForDashboard()}">dashboard</a> | <a href="/settings/">settings</a> | <a href="/logout/">logout</a></div>
            </div>
          </nav>
        </hgroup>
      </header>
    </div>
{else}
    <div id="splashHeader" class="container">
      <header>
        <hgroup>
          <h1>Cintient</h1>
          <img src="/imgs/redhalo.jpg" width="195" height="130">
        </hgroup>
      </header>
    </div>
{/if}
  </div>
{if isset($menuLeft) || isset($menuRight)}
  <div id="menu" class="containerTopLevel">
    <nav id="menuLeft">{if isset($menuLeft)}{$menuLeft}{/if}</nav>
    <nav id="menuRight">{if isset($menuRight)}{$menuRight}{/if}</nav>
  </div>
{/if}
  <div id="main" class="containerTopLevel">