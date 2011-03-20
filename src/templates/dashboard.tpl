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
{if !empty($dashboard_projectList)}
    <div id="projectListContainer" class="container">
      <ul>
{foreach $dashboard_projectList as $project}
{$dashboard_latestBuild=ProjectBuild::getLatest($project, $globals_user)}
      <li class="projectDraggableContainer container">
        <a href="{UrlManager::getForProjectView($project)}" class="projectLink">
        <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
        <div class="projectStatusContainer"><div class="projectStatus projectStatus{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Working{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}"><div class="projectStatusWaiting"></div></div></div>
        <div class="projectDetails">
          <div class="projectTitle">{$project->getTitle()}</div>
          <div class="projectStats">{if !empty($dashboard_latestBuild)}Latest: build {$dashboard_latestBuild->getId()}, r{$dashboard_latestBuild->getScmRevision()}, {if $dashboard_latestBuild->getStatus()!=ProjectBuild::STATUS_FAIL}built{else}failed{/if} on {$dashboard_latestBuild->getDate()|date_format}.{else}This project hasn't been built yet.{/if}</div>
          {if !empty($dashboard_latestBuild)}<div class="projectStats">Current version: {$dashboard_latestBuild->getLabel()}</div>{/if}
          {*<div class="projectStats">Production version: 1.0.9</div>*}
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
});
// ]]> 
</script>
{else}
    <div class="messageInfo container">You don't have any projects, but you can always <a href="{UrlManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}