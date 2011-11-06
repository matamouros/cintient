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
subSectionDescription="your projects at a glance"
subSectionId="dashboard"
jsIncludes=['js/lib/jquery.sparkline.min.js',
            'js/lib/bootstrap/bootstrap-tabs.js',
            'js/lib/highcharts-2.1.6.js',
            'js/lib/cintientHighcharts.theme.js',
						'js/lib/jquery.tablesorter.min.js']}
{if !empty($dashboard_projectList)}
    <div class="row">
      <div class="span5 leftRow">
        <ul id="projectList">
{foreach $dashboard_projectList as $project}

{$dashboard_latestBuild=Project_Build::getLatest($project, $globals_user)}
          <li class="project" id="{$project->getId()}">
            <ul>
              <li>
                <div class="col1">
                  <div class="projectAvatar40x40"><img src="{$project->getAvatarUrl()}" width="40" height="40"></div>
                </div>
                <div class="col2">
                <span class="label {if $project->getStatus()==Project::STATUS_OK}success{elseif $project->getStatus()==Project::STATUS_BUILDING}notice{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}warning{else}important{/if}">{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Building{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}</span>
                  <h3 class="projectTitle"><a href="{UrlManager::getForProjectEdit(['pid' => $project->getId()])}">{$project->getTitle()}</a></h3>
                </div>
              </li>
        			{if !empty($dashboard_latestBuild)}<li class="projectRevision"><a href="{UrlManager::getForProjectBuildHistory(['pid' => $project->getId()])}">#{$dashboard_latestBuild->getId()}, rev {$dashboard_latestBuild->getScmRevision()|truncate:8:''}</a></li>{/if}
              <li class="builtOn">{if !empty($dashboard_latestBuild)}Built on {$dashboard_latestBuild->getDate()|date_format:"%b %e, %Y at %R"}{else}This project hasn't been built yet{/if}</li>
          		{*if !empty($dashboard_latestBuild)}<li>Current version: {$dashboard_latestBuild->getLabel()}.</li>{/if*}
{if !empty($dashboard_latestBuild)}
              <li class="sparkline">
{$dashboard_buildList=Project_Build::getList($project, $globals_user, Access::READ, ['sort' => Sort::DATE_DESC])}
{$dashboard_buildList=array_reverse($dashboard_buildList)}
{$count=count($dashboard_buildList)}
          			{*Last {if $count!=1}{$count}{/if} build{if $count!=1}s{/if}:*}
                <div id="sparklineBuilds{$project->getId()}" class="sparklineBuilds">
{foreach $dashboard_buildList as $build}
  {if !$build@first}, {/if}
  {if $build->isOk()}1{else}-1{/if}
{/foreach}
                </div>
              </li>
{/if}
            </ul>
          </li>
{/foreach}
          </ul>
          </div>
          <div class="span11 rightRow" id="dashboardProject">
{include file='includes/dashboardProject.inc.tpl'
project = $dashboard_projectList.0
project_buildStats = Project_Build::getStats($dashboard_projectList.0, $globals_user)
project_build = Project_Build::getLatest($dashboard_projectList.0, $globals_user)
project_log = Project_Log::getList($dashboard_projectList.0, $globals_user)}
          </div>
        </div>
<script type="text/javascript">
// <![CDATA[
activeProjectId = {$dashboard_projectList.0->getId()};
$(document).ready(function() {
  Cintient.initSectionDashboard({
    submitUrl : '{UrlManager::getForAjaxDashboardProject()}'
  });
});
// ]]>
</script>
{else}
    <div>You don't have any projects yet, but you can always <a href="{UrlManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}