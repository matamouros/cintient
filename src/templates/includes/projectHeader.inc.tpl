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
    <article id="project">
      <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
      <div id="statusContainer"><div class="triggerBuild status projectStatus{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Working{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}"><div class="projectStatusWaiting"></div></div></div>
      <div class="title">{$project->getTitle()}</div>
      <div id="projectStatus_{$project->getId()}" class="details">
        {if !empty($project_latestBuild)}Latest: build {$project_latestBuild->getId()}, r{$project_latestBuild->getScmRevision()}, {if $project_latestBuild->getStatus()!=ProjectBuild::STATUS_FAIL}built{else}failed{/if} on {$project_latestBuild->getDate()|date_format}.{else}Click <a href="#" class="triggerBuild">here</a> to trigger the first build for this project.{/if}
      </div>
    </article>
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
    url: '{URLManager::getForAjaxProjectBuild()}',
    cache: false,
    dataType: 'json',
    success: function(data, textStatus, XMLHttpRequest) {
      console.log('a');
      if (!data.success) {
        console.log('b');
        //TODO: User notification of problems
        if (undefined === data.projectStatus) {
          console.log('c');
          data.projectStatus = projectLastKnownStatus;
        }
      }
      console.log('d');
      updateProjectStatus(data.projectStatus);
      console.log('e');
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      updateProjectStatus(projectLastKnownStatus);
      alert(errorThrown);
    }
  });

  //
  // Update the 
  //
}

function updateProjectStatus(toStatus)
{
  projectLastKnownStatus = toStatus;
  switch(toStatus) {
  case {Project::STATUS_OK}:
    $('#project #statusContainer .projectStatusWaiting').hide(50);
    $('#project #statusContainer .status').removeClass('projectStatusFailed projectStatusWorking');
    $('#project #statusContainer .status').addClass('projectStatusOk');
    break;
  case {Project::STATUS_BUILDING}:
    $('#project #statusContainer .status').removeClass('projectStatusFailed projectStatusOk');
    $('#project #statusContainer .status').addClass('projectStatusWorking');
    $('#project #statusContainer .projectStatusWaiting').fadeIn(150);
    break;
  default:
    $('#project #statusContainer .projectStatusWaiting').hide(50);
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
});
// ]]> 
</script>