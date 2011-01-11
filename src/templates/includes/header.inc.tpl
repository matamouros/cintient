{*
  Cintient, Continuous Integration made simple.
  
  Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  
  . Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
  
  . Redistributions in binary form must reproduce the above
    copyright notice, this list of conditions and the following
    disclaimer in the documentation and/or other materials provided
    with the distribution.
    
  . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
    the names of its contributors may be used to endorse or promote
    products derived from this software without specific prior
    written permission.
    
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
  
*}<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta property="url" content="{*SERVER_NAME*}">
  <title>Cintient</title>
  <link rel="stylesheet" href="/css/global.css" />
  <link rel="icon" href="/favicon.ico">
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
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
              <div id="avatar"><img src="/imgs/anon_avatar_50.png" width="40" height="40"></div>
              <div id="username">{$smarty.session.user->getUsername()}</div>
              <div id="links"><a href="/dashboard/">dashboard</a> | <a href="/settings/">settings</a> | <a href="/logout/">logout</a></div>
            </div>
            {*<a href="{URLManager::getForDashboard()}">dashboard</a> | <a href="{URLManager::getForProjectNew()}">new project</a>*}
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