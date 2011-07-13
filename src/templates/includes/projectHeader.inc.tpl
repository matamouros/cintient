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

*}
    <div id="projectHeader">
      <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
      <div id="statusContainer"><div class="triggerBuild status projectStatus{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Working{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}"><div class="projectStatusWaiting"></div></div></div>
      <div class="title">{$project->getTitle()}</div>
      <div id="buildListDropdownLink">
{if !empty($project_build)}
      <div id="projectStatus_{$project->getId()}" class="details">
        #{$project_build->getId()} r{$project_build->getScmRevision()},
        on {$project_build->getDate()|date_format:"%b %e, %Y at %R"}
      </div>
      <div class="dropdownTriangle"></div>
      </div>

      <div id="buildList" class="popupWidget">
{if !empty($project_buildList)}
        <table>
          <tbody>
{foreach from=$project_buildList item=build}
{$currentDate=$build->getDate()|date_format:"%b %e, %Y"}
{if $currentDate != $lastDate}
            <tr class="date">
              <th colspan="3">{$currentDate}</th>
            </tr>
{/if}
            <tr class="{UrlManager::getForProjectBuildView($globals_project, $build)}">
              <td><dt class="{if $build->getStatus()!=Project_Build::STATUS_FAIL}buildOk{else}buildFail{/if}">{$build->getDate()|date_format:"%R"}</dt></td>
              <td>#{$build->getId()}</td>
              <td>r{$build->getScmRevision()}</td>
            </tr>
{$lastDate=$build->getDate()|date_format:"%b %e, %Y"}
{/foreach}
          </tbody>
        </table>
{/if}
      </div>

{elseif $globals_project->userHasAccessLevel($globals_user, Access::BUILD) || $globals_user->hasCos(UserCos::ROOT)}
      <div id="projectStatus_{$project->getId()}" class="details">
        Click <a href="#" class="triggerBuild">here</a> to trigger the first build for this project.
      </div>
{/if}
    </div>
{if $globals_project->userHasAccessLevel($globals_user, Access::BUILD) || $globals_user->hasCos(UserCos::ROOT)}
<script type="text/javascript">
//<![CDATA[
var projectLastKnownStatus = {$globals_project->getStatus()};
function forceBuild()
{
  updateProjectStatus({Project::STATUS_BUILDING});
  //
  // XHR trigger the build
  //
  $.ajax({
    url: '{UrlManager::getForAjaxProjectBuild()}',
    cache: false,
    dataType: 'json',
    success: function(data, textStatus, XMLHttpRequest) {
      if (!data.success) {
        //TODO: User notification of problems
        if (undefined === data.projectStatus) {
          data.projectStatus = projectLastKnownStatus;
        }
      }
      updateProjectStatus(data.projectStatus);
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      updateProjectStatus(projectLastKnownStatus);
      alert(errorThrown);
    }
  });
}

function updateProjectStatus(toStatus)
{
  projectLastKnownStatus = toStatus;
  switch(toStatus) {
  case {Project::STATUS_OK}:
    $('#project #statusContainer .projectStatusWaiting').fadeOut(50);
    $('#project #statusContainer .status').removeClass('projectStatusFailed projectStatusWorking');
    $('#project #statusContainer .status').addClass('projectStatusOk');
    break;
  case {Project::STATUS_BUILDING}:
    $('#project #statusContainer .status').removeClass('projectStatusFailed projectStatusOk');
    $('#project #statusContainer .status').addClass('projectStatusWorking');
    $('#project #statusContainer .projectStatusWaiting').fadeIn(150);
    break;
  default:
    $('#project #statusContainer .projectStatusWaiting').fadeOut(50);
    $('#project #statusContainer .status').removeClass('projectStatusWorking projectStatusOk');
    $('#project #statusContainer .status').addClass('projectStatusFailed');
    break;
  }
}

$(document).ready(function() {
  //
  // Bind the project status icon to the build link
  //
  $('#project .triggerBuild').each(function() {
    $(this).click(function() {
      forceBuild();
    });
    $(this).hover(
      function() {
        $(this).css({
          "cursor" : "pointer",
        });
      },
      function() {
        $(this).css({
          "cursor" : "default",
        });
      });
  });

  //
  // The build list dropdown
  //
  buildListActive = false;
  $('#buildListDropdownLink').hover(
	  function() {
      $(this).css({
    	  "cursor" : "pointer"
      });
    },
    function() {
    	$(this).css({
    	  "cursor" : "default"
      });
    }
  );
  $('#buildListDropdownLink').click( function(e) {
    if (buildListActive) {
    	$('#buildList').fadeOut(50);
    } else {
      $('#buildList').fadeIn(50);
    }
    buildListActive = !buildListActive;
    e.stopPropagation();
  });
  $('#buildList table tr:not([class=date])').each( function() {
  	$(this).click(function() {
  		window.location = $(this).attr('class');
    });
  	$(this).hover(
  		function() {
        $(this).css({
      	  "cursor" : "pointer",
          "color" : "#555",
        	"text-shadow" : "1px 1px 1px #fff",
          "background" : "#ddd"
        });
      },
      function() {
      	$(this).css({
      	  "cursor" : "default",
          "color" : "#fff",
      		"text-shadow" : "1px 1px 1px #303030",
          "background" : "transparent"
        });
      });
  });

  // Close any menus on click anywhere on the page
  $(document).click(function(){
    if (buildListActive) {
      $('#buildList').fadeOut(50);
      buildListActive = false;
    }
  });
});
// ]]>
</script>
{/if}