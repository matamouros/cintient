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
{*$menuLinks="<span id=\"exclusivePaneLinks\"><a href=\"#\" class=\"deploymentBuilderPane\">deployment</a> | <a href=\"#\" class=\"integrationBuilderPane\">integration</a>"*}
{$menuLinks="<span id=\"exclusivePaneLinks\"><a href=\"#\" class=\"integrationBuilderPane\">integration</a>"}
{$defaultPane="#deploymentBuilder"}
{if $globals_project->userHasAccessLevel($globals_user, Access::WRITE) || $globals_user->hasCos(UserCos::ROOT)}
  {$menuLinks="$menuLinks | <a href=\"#\" class=\"generalPane\">general</a> | <a href=\"#\" class=\"scmPane\">scm</a>"}
  {$defaultPane="#generalPane"}
{/if}
{if $globals_project->userHasAccessLevel($globals_user, Access::OWNER) || $globals_user->hasCos(UserCos::ROOT)}
  {$menuLinks="$menuLinks | <a href=\"#\" class=\"usersPane\">users</a> | <a href=\"#\" class=\"notificationsPane\">notifications</a></span>"}
  {*$defaultPane="#generalPane"*}
  {$defaultPane="#integrationBuilderPane"}
{/if}
{include file='includes/header.inc.tpl'
  subSectionTitle="Edit project"
  menuLinks="<span id=\"exclusivePaneLinks\">$menuLinks</span>"
  backLink="{UrlManager::getForProjectView()}"
  jsIncludes=['js/lib/avataruploader.js', 'js/cintientNotifications.js']}
    <div id="paneContainer">
      <div id="generalPane" class="exclusivePane">
        <form action="{if isset($smarty.get.new)}{UrlManager::getForProjectNew()}{else}{UrlManager::getForProjectEdit()}{/if}" method="post">
        <div class="projectEditContainer container">
          <div class="label">Project avatar <span class="fineprintLabel">(click image to change it)</span></div>
          <div id="avatarUploader">
            <noscript>
              {* TODO: Use simple upload form *}
              <p>Please enable JavaScript to use file uploader.</p>
            </noscript>
          </div>
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
        {*</form>*}
      </div>
      <div id="notificationsPane" class="exclusivePane">
{foreach $globals_project->getNotificationsFromUser($globals_user) as $notification}
        <div id="{$notification->getMethod()}" class="projectEditContainer container">
          <div>{$notification->getMethod()}</div>
{$notification->getView()}
        </div>
{if !$notification@last}
        <hr />
{/if}
{/foreach}
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  new CintientNotifications({
    submitUrl: '{URLManager::getForAjaxProjectNotificationsSave()}',
  });
});
</script>
      </div>
      <div id="scmPane" class="exclusivePane">
        {* TODO: this should be a separate form so that we don't erroneously send a hidden tab's form that the user didn't intend to *}
        {*<form action="{if isset($smarty.get.new)}{UrlManager::getForProjectNew()}{else}{UrlManager::getForProjectEdit()}{/if}" method="post">*}
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
{if $globals_project->userHasAccessLevel($globals_user, Access::OWNER) || $globals_user->hasCos(UserCos::ROOT)}
      <div id="usersPane" class="exclusivePane">
        <div id="addUserPane" class="projectEditContainer container">
          <div class="label">Add an existing user <div class="fineprintLabel">(specify name or username)</div></div>
          <div class="textfieldContainer" style="width: 254px;">
            <input class="textfield" style="width: 250px;" type="search" id="searchUserTextfield" />
          </div>
          <div id="searchUserPane" class="popupWidget">
            <ul>
              <li></li>
            </ul>
          </div>
        </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  timerId = null;
  userTermVal = null;
  searchUserPaneActive = false;
  $('#searchUserTextfield').keyup(function(e) {
    userTermVal = $(this).val();
    if (userTermVal.length > 1) {
      triggerListRefresh = function() {
        //
        // TODO: Setup a spinning loading icon
        //
        $('#searchUserPane ul li').remove();
        $('#searchUserPane ul').append('<li class="spinningIcon"><img src="imgs/loading-3.gif" /></li>');
        $.ajax({
          url: '{UrlManager::getForAjaxSearchUser()}',
          data: { userTerm: userTermVal },
          type: 'GET',
          cache: false,
          dataType: 'json',
          success: function(data, textStatus, XMLHttpRequest) {
            $('#searchUserPane ul li').remove();
            if (!data.success) {
              $('#searchUserPane ul').append('<li>Problems fetching users.</li>');
            } else {
              if (data.result.length == 0) {
                $('#searchUserPane ul').append('<li>No users found.</li>');
              } else {
                found = 0
                for (i = 0; i < data.result.length; i++) {
                  if ($('ul#userList li#' + data.result[i].username).length == 0) {
                    $('#searchUserPane ul').append('<a href="#" class="'+data.result[i].username+'"><li><img class="avatar25" src="'+data.result[i].avatar+'"/><span class="username">'+data.result[i].username+'</span></li></a>');
                    found++;
                  }
                };
                if (found == 0) {
                  $('#searchUserPane ul').append('<li>No more users found.</li>');
                }
              }
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
          }
        });
        $('#searchUserPane').fadeIn(150);
        searchUserPaneActive = true;
      };
      if (timerId !== null) {
        clearTimeout(timerId); // Clear previous timers on queue
      }
      if (e.which == 13) { // Imediatelly send request, if ENTER was depressed
        triggerListRefresh();
      } else {
        timerId = setTimeout(triggerListRefresh, 1000);
      }
    }
  });
  //Close any menus on click anywhere on the page
  $(document).click(function(){
    if (searchUserPaneActive) {
      $('#searchUserPane').fadeOut(50);
      searchUserPaneActive = false;
    }
  });

  //
  // Add select widget user to the project list of users
  //
  $('#searchUserPane ul a').live('click', function() {
    $.ajax({
      url: '{UrlManager::getForAjaxProjectAddUser()}',
      data: { username: $(this).attr('class') },
      type: 'GET',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (!data.success) {
          $('ul#userList').append('<li>Problems adding user.</li>');
        } else {
          $('ul#userList').append(data.html);
          $('ul#userList li:last-child').slideDown(150);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert(errorThrown);
      }
    });
  });

  //
  // Remove user
  //
  $('ul#userList .remove a').live('click', function(e) {
    e.preventDefault();
    $.ajax({
      url: $(this).attr('href'),
      data: { username: $(this).attr('class') },
      type: 'GET',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (!data.success) {
          //TODO: treat this properly
          console.log('error');
        } else {
          slideUpTime = 150;
          $('ul#userList li#' + data.username).slideUp(slideUpTime);
          setTimeout(
            function() {
              $('ul#userList li#' + data.username).remove();
            },
            slideUpTime
          );
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert(errorThrown);
      }
    });
  });

  //
  // The project avatar uploader
  //
	var uploader = new qq.FileUploader({
    element: document.getElementById('avatarUploader'),
    action: '{UrlManager::getForAjaxAvatarUpload(['p'=>1])}',
    multiple: false,
    allowedExtensions: ['jpg', 'jpeg', 'png'],
    sizeLimit: {$smarty.const.CINTIENT_AVATAR_MAX_SIZE},
    onComplete: function(id, fileName, responseJSON) {
      $(".qq-upload-button").css({
        'background-image' : 'url(' + responseJSON.url + ')'
      });
    }
  });
});
//]]>
</script>
<style type="text/css">
.qq-upload-button
{
  background-image: url({$globals_project->getAvatarUrl()});
}
</style>
        <div class="projectEditContainer container">
          <ul id="userList">
{$accessLevels=Access::getList()}
{foreach from=$globals_project->getUsers() item=projectUser}
{$userAccessLevel=$projectUser->getAccess()}
{$user=$projectUser->getPtrUser()}
            <li id="{$user->getUsername()}">
              <div class="user">
                <div class="avatar"><img src="{$user->getAvatarUrl()}" width="40" height="40"></div>
                <div class="username">{$user->getUsername()}{if $user->getUsername()==$globals_user->getUsername()}<span class="fineprintLabel"> (this is you!){/if}</div>
{if !$globals_project->userHasAccessLevel($user, Access::OWNER)}
                <div class="remove"><a class="{$user->getUsername()}" href="{UrlManager::getForAjaxProjectRemoveUser()}">remove</a></div>
                <div class="accessLevelPane">
                  <div class="accessLevelPaneTitle"><a href="#" class="{$user->getUsername()}">access level</a></div>
                  <div id="accessLevelPaneLevels_{$user->getUsername()}" class="accessLevelPaneLevels">
                    <ul>
{foreach $accessLevels as $accessLevel => $accessName}
  {if $accessLevel !== 0} {* Don't show the NONE value access level *}
                      <li><input class="accessLevelPaneLevelsCheckbox" type="radio" value="{$user->getUsername()}_{$accessLevel}" name="accessLevel" id="{$accessLevel}" {if $userAccessLevel == $accessLevel} checked{/if} /><label for="{$accessLevel}" class="labelCheckbox">{$accessName|capitalize}<div class="fineprintLabel" style="display: none;">{Access::getDescription($accessLevel)}</div></label></li>
  {/if}
{/foreach}
                    </ul>
                  </div>
                </div>
{else}
                <div class="remove">Owner <span class="fineprintLabel">(no changes allowed)</span></div>
{/if}
              </div>
            </li>
{/foreach}
          </ul>
        </div>
      </div>
{/if}
      <div id="deploymentBuilderPane" class="exclusivePane">
        <div class="projectEditContainer container">
        </div>
      </div>
      <div id="integrationBuilderPane" class="exclusivePane">
        <div class="projectEditContainer container">
{include file='includes/builderEditor.inc.tpl'}
        </div>
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
  showExclusivePane($('{$defaultPane}'));

  //
  // For the access level panes
  //
  // Bind the click link events to their corresponding panes
  var cintientActivePane = null;
  $('.accessLevelPane .accessLevelPaneTitle a', $('#userList')).live('click', function(e) {
    if (cintientActivePane == null) {
      cintientActivePane = $('#usersPane .accessLevelPane #accessLevelPaneLevels_' + $(this).attr('class'));
      cintientActivePane.slideDown(100);
    } else {
      cintientActivePane.slideUp(100);
      cintientActivePane = null;
    }
    e.stopPropagation();
  });
  // Close any menus on click anywhere on the page
  $(document).click( function(e){
    if ($(e.target).attr('class') != 'accessLevelPaneLevels' &&
        $(e.target).attr('class') != 'accessLevelPaneLevelsCheckbox' &&
        $(e.target).attr('class') != 'labelCheckbox' ) {
      if (e.isPropagationStopped()) { return; }
      if (cintientActivePane != null) {
        cintientActivePane.slideUp(100);
        cintientActivePane = null;
      }
    }
  });
  //
  // Setup auto save for access level pane changes
  //
  $('.accessLevelPane input.accessLevelPaneLevelsCheckbox').live('click', function() {
    $.ajax({
      url: '{UrlManager::getForAjaxProjectAccessLevelChange()}',
      data: { change: $(this).attr('value') },
      type: 'GET',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (!data.success) {
          //TODO: treat this properly
          console.log('error');
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert(errorThrown);
      }
    });
    $('.accessLevelPane .accessLevelPaneLevels').fadeOut(300);
    cintientActivePane = null;
  });
});
//]]>
</script>
{include file='includes/footer.inc.tpl'}