{*
    Cintient, Continuous Integration made simple.
    Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>

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
  <link rel="stylesheet" href="css/lib/bootstrap-1.3.0.min.css" />
{foreach $cssIncludes as $cssInclude}
  <link rel="stylesheet" href="{$cssInclude}" />
{/foreach}
  <link rel="stylesheet" href="css/cintient.css" />
  <link rel="icon" href="www/imgs/favicon.ico" />
  <link rel="apple-touch-icon" href="www/imgs/favicon.ico" />
  <!--[if lt IE 9]>
  <script src="js/lib/html5.js"></script>
  <![endif]-->
  <link rel='index' title='Cintient' href='{*SERVER_NAME*}' />
  <meta name="generator" content="Cintient Engine" />
  <script type="text/javascript" src="js/lib/jquery-1.7.min.js"></script>
  <script type="text/javascript" src="js/lib/tipTipv13/jquery.tipTip.minified.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-alerts.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-dropdown.js"></script>
  <script type="text/javascript" src="js/lib/bootstrap/bootstrap-modal.js"></script>
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
        <ul class="nav">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="logoDropdown"><div class="cintientLettering" id="logoLettering">Cintient</div><span class="dropdownArrow">&darr;</span></a>
            <ul class="dropdown-menu">
              {if $globals_user instanceof User}
              <li><a href="{UrlManager::getForDashboard()}">Dashboard</a></li>
              <li><a href="{UrlManager::getForProjectNew()}">New project</a></li>
              {if $globals_user->hasCos(UserCos::ROOT)}<li class="divider"></li><li{if $globals_subSection == 'admin'} class="active"{/if}><a href="{UrlManager::getForAdmin()}">Administration</a></li>{/if}
              <li class="divider"></li>
              {/if}
              <li><a href="#">About</a></li>
            </ul>
          </li>
        </ul>
{if $globals_user instanceof User}
        <div class="menuLinks">
          <ul class="nav">
            <li{if $globals_subSection == 'dashboard'} class="active"{/if}><a href="{UrlManager::getForDashboard()}">Dashboard</a></li>
{*if $globals_section == 'project' && $globals_subSection != 'new'*}
{if $globals_project instanceof Project}
            <li class="dropdown{if $globals_section == 'project'} active{/if}">
              <a class="dropdown-toggle" href="#" id="activeProjectTitle">{$globals_project->getTitle()}</a>
              <ul class="dropdown-menu">
                <li{if $globals_subSection == 'history'} class="active"{/if}><a href="{UrlManager::getForProjectBuildHistory()}">Build history</a></li>
                <li{if $globals_subSection == 'edit'} class="active"{/if}><a href="{UrlManager::getForProjectEdit()}">Edit</a></li>
              </ul>
            </li>
{/if}
          </ul>
      		  {if !empty($menuLinks)}<ul class="sectionLinks">{$menuLinks}</ul>{/if}
          <ul class="secondary-nav">
            <li class="dropdown{if $globals_subSection == 'settings'} active{/if}">
              <a href="#" class="dropdown-toggle"><img id="avatarImg" src="{$globals_user->getAvatarUrl()}" width="30" height="30"> {$globals_user->getUsername()}</a>
              <ul class="dropdown-menu">
                <li{if $globals_subSection == 'settings'} class="active"{/if}><a href="{UrlManager::getForSettings()}">Settings</a></li>
                <li class="divider"></li>
                <li><a href="{UrlManager::getForLogout()}">Logout</a></li>
              </ul>
            </li>
          </ul>
        </div>
{/if}
      </div>
    </div>
  </div>
  <div id="alertPane"></div>
{if !empty($smarty.cookies.cintientInstalled)}
<!-- {setcookie('cintientInstalled', '', time()-3600)} -->
  <div id="modalPane" class="modal hide fade textLeft clearfix">
    <div class="modal-header">
      <a href="#" class="close">×</a>
      <h3>All ready to begin...</h3>
    </div>
    <div class="modal-body">
      <p>
        Thank you for trying out Cintient, an open source software made
        with some generous people's free time. This is an on-going effort,
        and we need all the help we can get.
      </p>
      <p>
        Support open source software: get up on GitHub.com, fork Cintient and contribute some pull requests,
        or fill in some bug reports.
      </p>
      <p>
        More info here: <a href="https://github.com/matamouros/cintient" target="_blank">https://github.com/matamouros/cintient</a>
      </p>
    </div>
    <div class="modal-footer">
      <a href="#" id="modalDismiss" class="btn primary">Sure thing!</a>
    </div>
  </div>
  <script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  $('#modalPane').modal({ show: true, backdrop: 'static', keyboard: true});
  $('#modalDismiss').on('click', function () {
    $('#modalPane').modal('hide');
  });
});
// ]]>
  </script>
{/if}
  <div class="container">
    <div class="mainContent" id="{$subSectionId}">
      <div class="page-header">
        {if !empty($subSectionImg)}<div class="projectAvatar40x40"><img src="{$subSectionImg}" width="40" height="40"></div>{/if}
        <h1>{$subSectionTitle} <small>{$subSectionDescription}</small></h1>{if !empty($subSectionInclude)}<div id="subSectionInclude">{include file=$subSectionInclude}</div>{/if}
      </div>
      <div id="sectionContent">{* This div makes sure the following is nicely wrapped in a properly float left container. FF was having issues *}