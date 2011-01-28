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
  subSectionTitle="Edit project"
  menuLinks="<span id=\"exclusivePaneLinks\"><a href=\"#\" class=\"deploymentBuilder\">deployment</a> | <a href=\"#\" class=\"integrationBuilder\">integration</a> | <a href=\"#\" class=\"metadataPane\">metadata</a> | <a href=\"#\" class=\"scmPane\">scm</a> | <a href=\"#\" class=\"usersPane\">users</a> | <a href=\"#\" class=\"deletePane\">delete</a></span>"
  backLink="{URLManager::getForProjectView()}"}
    <div id="paneContainer">
      <div id="metadataPane" class="exclusivePane">
        <form action="{if isset($smarty.get.new)}{URLManager::getForProjectNew()}{else}{URLManager::getForProjectEdit()}{/if}" method="post">
        <div class="projectEditContainer container">
          <div class="label">Project title</div>
          <div class="textfieldContainer" style="width: 404px;">
            <input class="textfield" style="width: 400px" type="text" name="title" value="{if isset($formData['title'])}{$formData['title']}{/if}">
          </div>
          <div class="label">A small description</div>
          <div class="textareaContainer">
            <textarea class="textarea" name="description">{if isset($formData['description'])}{$formData['description']}{/if}</textarea>
          </div>
          <div class="label">A build label</div>
          <div class="textfieldContainer" style="width: 364px;">
            <input class="textfield" style="width: 360px;" type="text" name="buildLabel" value="{if isset($formData['buildLabel'])}{$formData['buildLabel']}{/if}">
          </div>
          <input type="submit" value="Edit!" id="submitButton">
        </div>
        </form>
      </div>
      <div id="scmPane" class="exclusivePane">
        <form action="{if isset($smarty.get.new)}{URLManager::getForProjectNew()}{else}{URLManager::getForProjectEdit()}{/if}" method="post">
        <div class="projectEditContainer container">
          <div class="label">The SCM connector</div>
          <div class="dropdownContainer">
            <select class="dropdown" name="scmConnectorType">
{foreach from=$project_availableConnectors item=connector}
              <option value="{$connector}"{if isset($formData['scmConnectorType']) && $formData['scmConnectorType']==$connector} selected{/if}>{$connector|capitalize}
{/foreach}
            </select>
          </div>
          <div class="label">Username for SCM access</div>
          <div class="textfieldContainer" style="width: 304px;">
            <input class="textfield" style="width: 300px;" type="text" name="scmUsername" value="{if isset($formData['scmUsername'])}{$formData['scmUsername']}{/if}">
          </div>
          <div class="label">Password for SCM access</div>
          <div class="textfieldContainer" style="width: 304px;">
            <input class="textfield" style="width: 300px;" type="text" name="scmPassword" value="{if isset($formData['scmPassword'])}{$formData['scmPassword']}{/if}">
          </div>
          <div class="label">The SCM remote repository</div>
          <div class="textfieldContainer" style="width: 556px;">
            <input class="textfield" style="width: 550px;" type="text" name="scmRemoteRepository" value="{if isset($formData['scmRemoteRepository'])}{$formData['scmRemoteRepository']}{/if}">
          </div>
          <input type="submit" value="Edit!" id="submitButton">
        </div>
        </form>
      </div>
      <div id="usersPane" class="exclusivePane">
        <div class="projectEditContainer container">
{$accessLevels=Access::getList()}
{foreach from=$smarty.session.project->getUsers() item=user}
{$userAccessLevel=$user[1]}
{$user=User::getById($user[0])}
          <div id="{$user->getUsername()}" class="user">
            <div class="avatar"><img src="{$user->getAvatarUrl()}" width="40" height="40"></div>
            <div class="username">{$user->getUsername()}{if $user->getUsername()==$smarty.session.user->getUsername()}<span class="fineprintLabel"> (this is you!){/if}</div>
            <div class="accessLevelPane">
              <div class="accessLevelPaneTitle"><a href="#" class="{$user->getUsername()}">access level</a></div>
              <div id="accessLevelPaneLevels_{$user->getUsername()}" class="accessLevelPaneLevels">
                <ul>
{foreach $accessLevels as $accessLevel => $accessName}
  {if $accessLevel !== 0} {* Don't show the NONE value access level *}
                  <li><input type="checkbox" value="{$accessLevel}"{if ($userAccessLevel & $accessLevel)!=0} checked{/if}><div class="labelCheckbox">{$accessName|capitalize}</div></li>
  {/if}
{/foreach}
                </ul>
              </div>
            </div>
          </div>
{/foreach}
        </div>
      </div>
      <div id="deploymentBuilderPane" class="exclusivePane">
      </div>
      <div id="integrationBuilderPane" class="exclusivePane">
      </div>
    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  // Show the passed exclusivePane, hiding all others
  var activeExclusivePane = null;
  function showExclusivePane(exclusivePane) {
    if (activeExclusivePane === null || $(activeExclusivePane).attr('id') !== $(exclusivePane).attr('id')) {
      // Hide the previous pane
      $(activeExclusivePane).hide();
      // Reset the previous link
      $('#exclusivePaneLinks a.' + $(activeExclusivePane).attr('id')).css({
        "color" : "rgb(255,40,0)",
        "font-weight" : "bold",
        "text-decoration" : "none",
        "text-shadow" : "#303030 1px 1px 1px"
      });
      // Highlight the active link
      $('#exclusivePaneLinks a.' + $(exclusivePane).attr('id')).css({
        "color" : "rgb(255,60,0)",
        "text-shadow" : "0px 0px 6px rgba(255,40,0,1)",
        "text-decoration" : "none"
      });
      // Show the current pane
      exclusivePane.fadeIn(300);
      
      activeExclusivePane = exclusivePane;
    }
  }
  // Bind the click link events to their corresponding panes
  $('#exclusivePaneLinks a').each(function() {
    $(this).click(function() {
      showExclusivePane($('#paneContainer').find('#' + $(this).attr('class')));
    });
  });
  // Promptly show the default pane
  showExclusivePane($('#metadataPane'));

  //
  // For the access level panes
  //
  // Bind the click link events to their corresponding panes
  var cintientActivePane = null;
  $('.accessLevelPane .accessLevelPaneTitle a').each(function() {
    $(this).click(function(e) {
      e.stopPropagation();
      if (cintientActivePane == null) {
        cintientActivePane = $('#usersPane .accessLevelPane #accessLevelPaneLevels_' + $(this).attr('class'));
        cintientActivePane.fadeIn(100);
      } else {
        cintientActivePane.fadeOut(50);
        cintientActivePane = null;
      }
    });
  });
  // Close any menus on click anywhere on the page
  $(document).click(function(){
    if (cintientActivePane != null) {
      cintientActivePane.fadeOut(50);
      cintientActivePane = null;
    }
  });
});
//]]> 
</script>
{include file='includes/footer.inc.tpl'}