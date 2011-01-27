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
{if !empty($project_buildList)}
  {$menuRight="<span id=\"projectSectionsLinks\"><a href=\"#\" class=\"rawOutput\">raw</a> | <a href=\"#\" class=\"junitReport\">unit</a></span>"}
{/if}
{include file='includes/header.inc.tpl'
  menuLeft="Build history"
  menuRight=$menuRight}
    <article id="project">
      <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
      <div id="statusContainer"><div class="status projectStatus{if $smarty.session.project->getStatus()==Project::STATUS_OK}Ok{else}Failed{/if}"></div></div>
      <div class="title">{$smarty.session.project->getTitle()}</div>
      <div id="buildsList">
{if !empty($project_buildList)}
        <div class="label">Choose a different build:</div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  $('#buildsListDropdown').change(function() {
	  window.location.replace($(this).find("option:selected").attr('value'));
  });
});
//]]> 
</script>
        <select class="dropdown" id="buildsListDropdown">
{foreach from=$project_buildList item=build}
          <option value="{URLManager::getForProjectBuildView($smarty.session.project, $build)}"{if $build->getId()==$project_build->getId()} selected{/if}>build {$build->getId()}, r{$build->getScmRevision()} {if $build->getStatus()!=ProjectBuild::STATUS_FAIL}built{else}failed{/if} on {$build->getDate()|date_format}
{/foreach}
        </select>
{/if}  
      </div>
      <div class="details">{if !empty($project_buildList)}Latest: build {$project_buildList.0->getId()}, r{$project_buildList.0->getScmRevision()}, {if $project_buildList.0->getStatus()!=ProjectBuild::STATUS_FAIL}built{else}failed{/if} on {$project_buildList.0->getDate()|date_format}.{else}Click <a href="{URLManager::getForAjaxProjectBuild()}">here</a> to trigger the first build for this project.{/if}</div>
    </article>
{if !empty($project_buildList)}
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	// Show the passed resultPane, hiding all others
	var activeResultPane = null;
	function showBuildResultPane(resultPane) {
		if (activeResultPane === null || $(activeResultPane).attr('id') !== $(resultPane).attr('id')) {
			// Hide the previous pane
      $(activeResultPane).hide(50);
			// Reset the previous link
      $('#projectSectionsLinks a.' + $(activeResultPane).attr('id')).css({
        "color" : "rgb(255,40,0)",
        "font-weight" : "bold",
        "text-decoration" : "none",
        "text-shadow" : "#303030 1px 1px 1px"
      });
			// Highlight the active link
			$('#projectSectionsLinks a.' + $(resultPane).attr('id')).css({
				"color" : "rgb(255,60,0)",
			  "text-shadow" : "0px 0px 6px rgba(255,40,0,1)",
			  "text-decoration" : "none"
      });
		  // Show the current pane
  	  resultPane.fadeIn(300);
  	  
  	  activeResultPane = resultPane;
		}
  }
	// Bind the click link events to their corresponding panes
	$('#projectSectionsLinks a').each(function() {
		$(this).click(function() {
			showBuildResultPane($('#projectViewContainer').find('#' + $(this).attr('class')));
    });
  });
	// Promptly show the default pane
	showBuildResultPane($('#projectViewContainer #junitReport'));
});
//]]> 
</script>
    <div id="projectViewContainer">
      <div id="rawOutput" class="buildResultPane">{$project_build->getOutput()|nl2br}</div>
      <div id="junitReport" class="buildResultPane">
{if !empty($project_buildJunit)}
{foreach from=$project_buildJunit item=classTest}
        <div class="classTest">{$classTest->getName()}</div>
        <div class="chart"><img width="{$smarty.const.CHART_JUNIT_DEFAULT_WIDTH}" src="{URLManager::getForAsset($classTest->getChartFilename(), ['bid' => $project_build->getId()])}"></div>
{/foreach}
{else}
Due to a build error, the unit tests chart could not be generated. Please check the raw output of the build for problems, such as a PHP Fatal error.
{/if}
    </div>
{/if}
{include file='includes/footer.inc.tpl'}