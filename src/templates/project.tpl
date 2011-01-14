{*
  Cintient, Continuous Integration made simple.
  
  Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  
  . Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
  
  . Redistributions in binary form must reproduce the above
    copyright notice, this list of conditions and the following
    disclaimer in the documentation and/or other materials provided
    with the distribution.
    
  . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
    the names of its contributors may be used to endorse or promote
    products derived from this software without specific prior
    written permission.
    
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
  
*}{if isset($smarty.get.new)}
{include file='includes/header.inc.tpl' menuLeft="New project" menuRight="Create a new project below, or <a href=\"{URLManager::getForDashboard()}\">cancel</a>."}
{* PROJECT NEW *}
    <form action="{URLManager::getForProjectNew()}" method="post">
    <div id="newProjectContainer" class="container">
      <div class="label">Project title</div>
      <div class="textfieldContainer" style="width: 204px;">
        <input class="textfield" style="width: 200px" type="text" name="title">
      </div>
      <div class="label">A small description</div>
      <div class="textareaContainer">
        <textarea class="textarea" name="description"></textarea>
      </div>
      <div class="label">A build label</div>
      <div class="textfieldContainer" style="width: 164px;">
        <input class="textfield" style="width: 160px;" type="text" name="buildLabel">
      </div>
      <div class="label">The SCM connector</div>
      <div class="dropdownContainer">
        <select class="dropdown" name="scmConnectorType">
{foreach from=$project_availableConnectors item=connector}
          <option value="{$connector}">{$connector|capitalize}
{/foreach}
        </select>
      </div>
      <div class="label">Username for SCM access</div>
      <div class="textfieldContainer" style="width: 164px;">
        <input class="textfield" style="width: 160px;" type="text" name="scmUsername">
      </div>
      <div class="label">Password for SCM access</div>
      <div class="textfieldContainer" style="width: 164px;">
        <input class="textfield" style="width: 160px;" type="password" name="scmPassword">
      </div>
      <div class="label">The SCM remote repository</div>
      <div class="textfieldContainer" style="width: 356px;">
        <input class="textfield" style="width: 350px;" type="text" name="scmRemoteRepository">
      </div>
      <input type="submit" value="Create!" id="submitButton">
    </div>
    </form>
{else}
{include file='includes/header.inc.tpl'
  menuRight="<a href=\"{URLManager::getForProjectBuild($smarty.session.project)}\">force build</a> | <a href=\"{URLManager::getForProjectEdit($smarty.session.project)}\">edit</a>"}
{* PROJECT DETAILS *}
    <article id="project">
      <div class="avatar"><img src="/imgs/redhalo_90x90.jpg"></div>
      <div id="statusContainer"><div class="status projectStatus{if $smarty.session.project->getStatus()==Project::STATUS_OK}Ok{else}Failed{/if}"></div></div>
      <div class="title">{$smarty.session.project->getTitle()}</div>
      <div class="details">
        <div class="stats">{if !empty($project_buildList)}Latest: build {$project_buildList.0->getId()}, r{$project_buildList.0->getScmRevision()}, {if $project_buildList.0->getStatus()!=ProjectBuild::STATUS_FAIL}built{else}failed{/if} on {$project_buildList.0->getDate()|date_format}.{else}Click <a href="{URLManager::getForProjectBuild($smarty.session.project)}">here</a> to trigger the first build for this project.{/if}</div>
        <div id="users">
          <div class="title">Registered users for this project:</div>
{foreach from=$smarty.session.project->getUsers() item=user}
{$username=User::getById($user[0])->getUsername()}
          <div class="user">
            <div class="avatar"><img src="/imgs/anon_avatar_50.png" width="25" height="25"></div>
            <div class="username">{if $username==$smarty.session.user->getUsername()}This is you!{else}{$username}{/if}</div>
          </div>
{/foreach}
        </div>
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
      </div>
    </article>
{if !empty($project_buildList)}
    <nav id="projectSectionsLinks">
      <a href="#" class="rawOutput">raw output</a> | <a href="#" class="junitReport">unit tests</a>
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
        "text-shadow" : "none"
      });
			// Highlight the active link
			$('#projectSectionsLinks a.' + $(resultPane).attr('id')).css({
				"color" : "rgb(255,60,0)",
			  "text-shadow" : "0px 0px 6px rgba(255,40,0,1)",
			  "text-decoration" : "none"
      });
		  // Show the current pane
  	  resultPane.show(200);
  	  
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
    </nav>
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
    </div>
{/if}
{include file='includes/footer.inc.tpl'}