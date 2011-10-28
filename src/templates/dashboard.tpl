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
subSectionId="dashboard"
jsIncludes=['js/lib/jquery.sparkline.min.js']}
{if !empty($dashboard_projectList)}
    <div class="row">
      <div class="span5 leftRow">
        <ul id="projectList">
{foreach $dashboard_projectList as $project}
{$dashboard_latestBuild=Project_Build::getLatest($project, $globals_user)}
          <li class="project">
            <ul>
              <li>
                <div class="col1">
                  <div class="projectAvatar40x40"><img src="{$project->getAvatarUrl()}" width="40" height="40"></div>
                </div>
                <div class="col2">
                <span class="label {if $project->getStatus()==Project::STATUS_OK}success{elseif $project->getStatus()==Project::STATUS_BUILDING}notice{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}warning{else}important{/if}">{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Building{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}</span>
                  <h3 class="projectTitle"><a href="{UrlManager::getForProjectView($project)}" class="projectLink">{$project->getTitle()}</a></h3>
                </div>
              </li>
        			{if !empty($dashboard_latestBuild)}<li class="projectRevision">#{$dashboard_latestBuild->getId()}, rev {$dashboard_latestBuild->getScmRevision()|truncate:8:''}</li>{/if}
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
          <div class="span11 rightRow">
            asjdoaij oajsdoiaj sdoaij sdoai jsdoaij sdoai jdoai jdoi
          </div>
        </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionDashboard();
});
// ]]>
</script>
{else}
    <div>You don't have any projects, but you can always <a href="{UrlManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}