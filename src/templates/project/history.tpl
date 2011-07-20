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
{* These two captures insure initialization, so that $smarty.capture  *}
{* array always exists for these vars, even in case no special tasks  *}
{* were defined for the current build.                                *}
{capture name="specialTaskLink"}{/capture}
{capture name="specialTaskPane"}{/capture}
{$specialTaskPanes=array()}
{$menuLinks="<span id=\"projectSectionsLinks\"><a href=\"#\" class=\"rawOutput\">raw</a>"}
{foreach $project_specialTasks as $task}
  {include file="includes/specialTask/$task.inc.tpl"}
  {$specialTaskLink=$smarty.capture.specialTaskLink}
  {* We should be using capture append for specialTaskPane, but apparently *}
  {* it always only holds the last value... Come back to this later.       *}
  {$specialTaskPanes[]=$smarty.capture.specialTaskPane}
  {$menuLinks="$menuLinks | $specialTaskLink"}
{/foreach}
{$menuLinks="$menuLinks</span>"}
{include file='includes/header.inc.tpl'
  subSectionTitle="Build history"
  menuLinks=$menuLinks
  backLink="{UrlManager::getForProjectView()}"}
{$project_latestBuild=""}
{if !empty($project_buildList)}
  {$project_latestBuild=$project_buildList.0}
{/if}
{include file='includes/projectHeader.inc.tpl' project=$globals_project project_latestBuild=$project_latestBuild}
{if !empty($project_buildList)}
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	// Show the passed resultPane, hiding all others
	activeResultPane = null; // Needs to be global so that the project header build list knows where to change to if another build is chosen
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
  defaultPane = '#rawOutput';
  if (window.location.hash != '' && $('#projectViewContainer ' + window.location.hash).length>0) {
    defaultPane = window.location.hash;
  }
	showBuildResultPane($('#projectViewContainer ' + defaultPane));
});
//]]>
</script>
    <div id="projectViewContainer">
      <div id="rawOutput" class="buildResultPane rawText">{$project_build->getOutput()|raw2html}</div>
{foreach $specialTaskPanes as $taskPane}
{$taskPane}
{/foreach}
    </div>
{/if}
{include file='includes/footer.inc.tpl'}