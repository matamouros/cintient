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
{include file='includes/header.inc.tpl'
  subSectionId="projectEdit"
  subSectionTitle=$globals_project->getTitle()
  subSectionDescription="Edit project"
	subSectionImg=$globals_project->getAvatarUrl()
  jsIncludes=['js/lib/avataruploader.js',
              'js/lib/bootstrap/bootstrap-tabs.js']
  cssIncludes=['css/lib/avataruploader.css']}

    <ul class="tabs">
{if $globals_project->userHasAccessLevel($globals_user, Access::READ) || $globals_user->hasCos(UserCos::ROOT)}
{* The links appear for READ access. But submitting should only be allowed for WRITE *}
      <li><a href="#integration">Integration builder</a></li>
      <li class="active"><a href="#general">General</a></li>
      <li><a href="#scm">SCM</a></li>
      <li><a href="#notifications">Notifications</a></li>
{/if}
{if $globals_project->userHasAccessLevel($globals_user, Access::OWNER) || $globals_user->hasCos(UserCos::ROOT)}
      <li><a href="#users">Users</a></li>
      <li><a href="#delete">Delete</a></li>
{/if}
    </ul>

    <div class="pill-content">
      <div id="general" class="active">
        <form action class="form" id="generalForm">
          <fieldset>
            <div class="clearfix">
              <label for="avatarUploader">Project avatar</label>
              <div id="avatarUploader">
                <noscript>
                  {* TODO: Use simple upload form *}
                  <p>Please enable JavaScript to use file uploader.</p>
                </noscript>
              </div>
              <span class="help-inline">Click image to change it.</span>
            </div>
            <div class="clearfix">
              <label for="title">Project title</label>
              <div class="input">
                <input class="span7" type="text" name="title" value="{$globals_project->getTitle()}">
              </div>
            </div>
            <div class="clearfix">
              <label for="buildLabel" class="tooltip" title="This will be used to name the release package files.">A build label</label>
              <div class="input">
                <input class="span6" type="text" name="buildLabel" value="{$globals_project->getBuildLabel()}">
              </div>
            </div>
            <div class="clearfix">
              <label for="description">A small description</label>
              <div class="input">
                <textarea class="xxlarge" rows="3" name="description">{$globals_project->getDescription()}</textarea>
              </div>
            </div>
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
            </div>
          </fieldset>
        </form>
      </div>

      <div id="notifications">
        <form action class="form" id="notificationsForm">
          <fieldset>
{$projectUser=Project_User::getByUser($globals_project, $globals_user)}
{$notifications=$projectUser->getNotifications()}
{$notifications->getView()}
          </fieldset>
          <div class="actions">
            <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
          </div>
        </form>
      </div>

      <div id="scm">
        <form action class="form" id="scmForm">
          <fieldset>
            <div class="clearfix">
              <label for="scmConnectorType">The SCM connector</label>
              <div class="input">
                <select class="span2" name="scmConnectorType">
{foreach from=$project_availableConnectors item=connector}
                  <option value="{$connector}"{if $globals_project->getScmConnectorType()==$connector} selected{/if}>{$connector|capitalize}
{/foreach}
                </select>
              </div>
            </div>
            <div class="clearfix">
              <label for="scmRemoteRepository">The SCM remote repository</label>
              <div class="input">
                <input class="span10" type="text" name="scmRemoteRepository" value="{$globals_project->getScmRemoteRepository()}">
              </div>
            </div>
            <div class="clearfix">
              <label for="scmUsername">Username for SCM access</label>
              <div class="input">
                <input class="span6" type="text" name="scmUsername" value="{$globals_project->getScmUsername()}">
                <span class="help-block">This field is optional.</span>
              </div>
            </div>
            <div class="clearfix">
              <label for="scmPassword">Password for SCM access</label>
              <div class="input">
                <input class="span6" type="text" name="scmPassword" value="{$globals_project->getScmPassword()}">
                <span class="help-block">This field is optional.</span>
              </div>
            </div>
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
            </div>
      	  </fieldset>
        </form>
      </div>

      <div id="delete">
        <form action class="form" id="deleteForm">
          <fieldset>
            <div class="clearfix error">
              <h3>Do you really want to delete {$globals_project->getTitle()}? This action is irreversible.</h3>
              {*<label id="pid" class="span3">Check this if you agree</label>*}
              <div class="input">
                <ul class="inputs-list">
                  <li>
                    <label>
                      <input type="checkbox" id="pid" name="pid" value="{$globals_project->getId()}">
                      <span>I understand this action is irreversible.</span>
                    </label>
                  </li>
                </ul>
                <span class="help-block">
                  <strong>Note:</strong> to delete the project, check this and then click the red button.
                </span>
              </div>
              {*<input type="hidden" value="{$globals_project->getId()}" name="pid">*}
            </div>
            <div class="actions">
              <button class="btn danger disabled" id="deleteBtn" disabled="disabled">Yes, I really want to delete this project!</button>&nbsp;<button class="btn" id="cancelBtn">Cancel</button>
            </div>
          </fieldset>
        </form>
      </div>

{if $globals_project->userHasAccessLevel($globals_user, Access::OWNER) || $globals_user->hasCos(UserCos::ROOT)}
      <div id="users">
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
      // Update all the avatars on the current page
      $(".projectAvatar40x40 img").attr('src', responseJSON.url);
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
      <div id="integration">
        <form action class="form" id="integrationForm">
          <fieldset>
{include file='includes/builderEditor.inc.tpl'}
          </fieldset>
        </form>
      </div>

    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionProjectEdit();

  Cintient.initGenericForm({
    formSelector : '#general form',
    submitUrl: '{URLManager::getForAjaxProjectEditGeneral()}',
  });
  Cintient.initGenericForm({
    formSelector : '#notifications form',
    submitUrl: '{URLManager::getForAjaxProjectNotificationsSave()}',
  });
  Cintient.initGenericForm({
    formSelector : '#scm form',
    submitUrl: '{URLManager::getForAjaxProjectEditScm()}',
  });
  Cintient.initGenericForm({
    formSelector : '#delete form',
    onSuccessRedirectUrl : '{UrlManager::getForDashboard()}',
    submitUrl : '{UrlManager::getForAjaxProjectDelete()}',
    successMsg : 'Deleted!',
  });











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