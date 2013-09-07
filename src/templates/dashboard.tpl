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

*}{include file='includes/header.inc.tpl'
subSectionTitle="Dashboard"
subSectionDescription="your projects at a glance"
subSectionId="dashboard"
jsIncludes=['js/lib/jquery.sparkline.min.js',
            'js/lib/bootstrap/bootstrap-tabs.js',
            'js/lib/highcharts.js',
            'js/lib/cintientHighcharts.theme.js',
						'js/lib/jquery.tablesorter.min.js']
cssIncludes=[]}
{if !empty($dashboard_projectList)}
    <div class="row">
      <div class="span5 leftRow">
        <ul id="projectList">
{foreach $dashboard_projectList as $project}

{$dashboard_latestBuild=Project_Build::getLatest($project, $globals_user)}
          <li class="project" id="{$project->getId()}">
            <ul>
              <li class="{$project->getStatus()}">
                <div class="col1">
                  <div class="projectAvatar40x40"><img src="{$project->getAvatarUrl()}" width="40" height="40"></div>
                </div>
                <div class="col2">
                  <div class="projectStatus"><span class="label {if $project->getStatus() == Project::STATUS_OK}success{elseif $project->getStatus() == Project::STATUS_BUILDING}notice{elseif $project->getStatus() == Project::STATUS_ERROR}warning{elseif $project->getStatus() == Project::STATUS_FAILED}important{/if}">{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Building{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{elseif $project->getStatus()==Project::STATUS_ERROR}Error{elseif $project->getStatus()==Project::STATUS_FAILED}Failed{/if}</span></div>
                  <h3 class="projectTitle"><a href="{UrlManager::getForProjectEdit(['pid' => $project->getId()])}">{$project->getTitle()}</a></h3>
                </div>
{if $project->userHasAccessLevel($globals_user, Access::BUILD) || $globals_user->hasCos(UserCos::ROOT)}
                <div class="col3">
                  <button class="disabled build btn small">Build</button>
                  <div class="loading"><img src="imgs/loading-3.gif" /></div>
                </div>
{/if}
{if $dashboard_latestBuild instanceof Project_Build}
	{$externalCommitLink=UrlManager::getExternalForScmCommitLink($project, $dashboard_latestBuild)}
{else}
  {$externalCommitLink=''}
{/if}
              </li>
        			{if !empty($dashboard_latestBuild)}<li class="projectRevision"><a href="{UrlManager::getForProjectBuildHistory(['pid' => $project->getId()])}">#{$dashboard_latestBuild->getId()}</a>, {if $project->getScmConnectorType() == 'svn'}r{/if}{if !empty($externalCommitLink)}<a href="{$externalCommitLink}" target="_blank">{/if}{$dashboard_latestBuild->getScmRevision()|truncate:10:''}{if !empty($externalCommitLink)}</a>{/if}</li>{/if}
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
{* $globals_project must be guaranteed to always exist with a project *}
{*include file='includes/dashboardProject.inc.tpl'
project = $globals_project
project_buildStats = Project_Build::getStats($globals_project, $globals_user)
project_build = Project_Build::getLatest($globals_project, $globals_user)
project_log = Project_Log::getList($globals_project, $globals_user)*}
          </div>
        </div>
<script type="text/javascript">
// <![CDATA[
activeProjectId = {$globals_project->getId()};
firstLoad = true;
$(document).ready(function() {
  Cintient.initSectionDashboard({
    submitUrl : '{UrlManager::getForAjaxDashboardProject()}'
  });

  function hideLoading(pid)
  {
    $('#' + pid + ' .col3 .loading').hide();
    if (activeProjectId == pid) {
      $('#' + pid + ' .col3 button').fadeIn(300);
    }
  }

  function showLoading(pid)
  {
    $('#' + pid + ' .col3 button').hide();
    $('#' + pid + ' .col3 .loading').fadeIn(300);
  }

  function forceBuild(e, pid)
  {
    $('li#' + pid).trigger('click');
    showLoading(pid);
    updateProjectStatus(pid, {Project::STATUS_BUILDING});
    e.stopPropagation();
    //
    // XHR trigger the build
    //
    var that = this;
    $.ajax({
      url: '{UrlManager::getForAjaxProjectBuild()}',
      data: { pid: pid },
      cache: false,
      dataType: 'json',

      success: function(data, textStatus, XMLHttpRequest) {
        if (data == null || data.success == null) {
          Cintient.alertUnknown();
          data = {
            success: false,
            projectStatus: $(that).prop('class')
          };
        } else if (!data.success) {
          Cintient.alertFailed(data.error);
        }
        hideLoading(pid);
        updateProjectStatus(pid, data.projectStatus);
        $(that).removeClass().addClass(data.projectStatus);
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        hideLoading(pid);
      }
    });

  }

  function updateProjectStatus(pid, toStatus)
  {
    switch (toStatus) {
    case {Project::STATUS_OK}:
      projectLastKnownStatus = toStatus;
      $('#' + pid + ' .projectStatus').hide();
      $('#' + pid + ' .projectStatus span')
        .removeClass('notice warning important')
        .addClass('success')
        .text('ok');
      $('#' + pid + ' .projectStatus').fadeIn(300);
      break;
    case {Project::STATUS_BUILDING}:
      $('#' + pid + ' .projectStatus').hide();
      $('#' + pid + ' .projectStatus span')
        .removeClass('success warning important')
        .addClass('notice')
        .text('building');
      $('#' + pid + ' .projectStatus').fadeIn(300);
      break;
    case {Project::STATUS_ERROR}:
      $('#' + pid + ' .projectStatus').hide();
      $('#' + pid + ' .projectStatus span')
        .removeClass('success notice important')
        .addClass('warning')
        .text('error');
      $('#' + pid + ' .projectStatus').fadeIn(300);
      break;
    default:
      projectLastKnownStatus = toStatus;
      $('#' + pid + ' .projectStatus').hide();
      $('#' + pid + ' .projectStatus span')
        .removeClass('success notice warning')
        .addClass('important')
        .text('failed');
      $('#' + pid + ' .projectStatus').fadeIn(300);
      break;
    }
  }

  $('#dashboard li.project .build').on('click', function (e) {
    forceBuild(e, $(this).parents('li.project').prop('id'));
  });

  $('li#' + activeProjectId).trigger('click');
});
// ]]>
</script>
{else}
    <div>You don't have any projects yet, but you can always <a href="{UrlManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}