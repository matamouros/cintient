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

*}{include file='includes/header.inc.tpl'
  subSectionTitle="Dashboard"
  menuLinks="<a href=\"{UrlManager::getForProjectNew()}\">new project</a>"}
    <script type="text/javascript" src="/js/lib/jquery.sparkline.min.js"></script>
{if !empty($dashboard_projectList)}
    <div id="projectListContainer" class="container">
      <ul>
{foreach $dashboard_projectList as $project}
{$dashboard_latestBuild=Project_Build::getLatest($project, $globals_user)}
      <li class="projectDraggableContainer container">
        <a href="{UrlManager::getForProjectView($project)}" class="projectLink">
        <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
        <div class="projectStatusContainer"><div class="projectStatus projectStatus{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Working{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}"><div class="projectStatusWaiting"></div></div></div>
        <div class="projectDetails">
          <div class="projectTitle">{$project->getTitle()}</div>
          <div class="projectStats">{if !empty($dashboard_latestBuild)}Latest: #{$dashboard_latestBuild->getId()} r{$dashboard_latestBuild->getScmRevision()}, on {$dashboard_latestBuild->getDate()|date_format:"%b %e, %Y at %R"}.{else}This project hasn't been built yet.{/if}</div>
          {if !empty($dashboard_latestBuild)}<div class="projectStats">Current version: {$dashboard_latestBuild->getLabel()}</div>{/if}
          {*<div class="projectStats">Production version: 1.0.9</div>*}
        </div>
        <div class="sparkline">
{$dashboard_buildList=Project_Build::getList($project, $globals_user, Access::READ, ['sort' => Sort::DATE_ASC])}
          <div class="sparklineTitle">last {count($dashboard_buildList)} builds</div>
          <div id="sparklineBuilds" style="display: hidden;">
{foreach $dashboard_buildList as $build}
  {if !$build@first}, {/if}
  {if $build->isOk()}1{else}-1{/if}
{/foreach}
          </div>
        </div>
        </a>
      </li>
{/foreach}
      </ul>
    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  $('.projectDraggableContainer').each( function() {
  	$(this).click(function() {
  		window.location = $(this).find('a').attr('href');
    });
  	$(this).hover(
  		function() {
        $(this).css({
      	  "cursor" : "pointer",
      	  "border" : "2px solid rgb(255,40,0)",
      	  "box-shadow" : "0px 0px 40px rgb(255,40,0)",
          "-webkit-box-shadow" : "rgb(255,40,0) 0px 0px 40px",
          "-moz-box-shadow" : "rgb(255,40,0) 0px 0px 30px"
        });
      },
      function() {
      	$(this).css({
      	  "cursor" : "default",
      	  "border" : "2px solid #999",
      	  "box-shadow" : "2px 2px 10px #111",
      	  "-webkit-box-shadow" : "#111 2px 2px 10px",
      	  "-moz-box-shadow" : "#111 2px 2px 10px"
        });
      });
  });
  //
  // The sparklines
  //
  $('#sparklineBuilds').sparkline('html', {
    type: 'tristate',
    posBarColor: 'rgb(124,196,0)',
    negBarColor: 'rgb(255,40,0)'
  });
});
// ]]>
</script>
{else}
    <div class="messageInfo container">You don't have any projects, but you can always <a href="{UrlManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}