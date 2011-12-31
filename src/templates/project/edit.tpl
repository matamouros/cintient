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
subSectionId="projectEdit"
subSectionTitle=$globals_project->getTitle()
subSectionDescription="edit project"
subSectionImg=$globals_project->getAvatarUrl()
cssIncludes=['css/lib/avataruploader.css']
jsIncludes=['js/lib/jquery-ui-1.8.16.custom.min.js',
            'js/lib/avataruploader.js',
            'js/lib/bootstrap/bootstrap-tabs.js']}
{* Holy shit... You cannot add jquery-ui jsInclude after the bootstrap-tabs
   one, or else tabs simply won't work... *}

    <ul class="tabs">
{if $globals_project->userHasAccessLevel($globals_user, Access::READ) || $globals_user->hasCos(UserCos::ROOT)}
{* The links appear for READ access. But submitting should only be allowed for WRITE *}
      <li><a href="#integration">Build</a></li>
      <li><a href="#release">Release</a></li>
      {*<li><a href="#deployment">Deployment build</a></li>*}
      {*<li class="dropdown" data-dropdown="dropdown">
        <a href="#" class="dropdown-toggle">Builders</a>
        <ul class="dropdown-menu">
          <li><a href="#integration">Integration</a></li>
          <li><a href="#release">Release</a></li>
        </ul>
      </li>*}
      <li class="active"><a href="#general">General</a></li>
      <li><a href="#scm">SCM</a></li>
      <li><a href="#notifications">Notifications</a></li>
{/if}
{if $globals_project->userHasAccessLevel($globals_user, Access::OWNER) || $globals_user->hasCos(UserCos::ROOT)}
      <li><a href="#users">Users</a></li>
      <li><a href="#delete">Delete</a></li>
{/if}
    </ul>
<style type="text/css">
.qq-upload-button
{
  background-image: url({$globals_project->getAvatarUrl()});
}
</style>
    <div class="tab-content">
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
                <input class="span7" type="text" name="title" value="{$globals_project->getTitle()}" />
              </div>
            </div>
            <div class="clearfix">
              <label for="description">A small description</label>
              <div class="input">
                <textarea class="xxlarge" rows="3" name="description">{$globals_project->getDescription()}</textarea>
              </div>
            </div>
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes" />&nbsp;<button type="reset" class="btn">Cancel</button>
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
            <input type="submit" class="btn primary" value="Save changes" />&nbsp;<button type="reset" class="btn">Cancel</button>
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
                <input class="span10" type="text" name="scmRemoteRepository" value="{$globals_project->getScmRemoteRepository()}" />
              </div>
            </div>
            <div class="clearfix">
              <label for="scmUsername">Username for SCM access</label>
              <div class="input">
                <input class="span6" type="text" name="scmUsername" value="{$globals_project->getScmUsername()}" />
                <span class="help-block">This field is optional.</span>
              </div>
            </div>
            <div class="clearfix">
              <label for="scmPassword">Password for SCM access</label>
              <div class="input">
                <input class="span6" type="text" name="scmPassword" value="{$globals_project->getScmPassword()}" />
                <span class="help-block">This field is optional.</span>
              </div>
            </div>
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes" />&nbsp;<button type="reset" class="btn">Cancel</button>
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
                      <input type="checkbox" id="pid" name="pid" value="{$globals_project->getId()}" />
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
        <div id="usersPane">
          <div id="searchResultsPopover" class="popover-wrapper">
            <div class="popover right fade in">
              <div class="arrow"></div>
              <div class="inner">
                <h3 class="title">Search results</h3>
                <div class="content">
                  <ul><li></li></ul>
                </div>
              </div>
            </div>
          </div>
          <form action class="form" id="usersForm">
            <fieldset>
              <div class="clearfix">
                <label for="search">Add an existing user to the project</label>
                <div class="input">
                  <input class="span5" type="search" name="search" id="searchUserTextfield" />
                  <span class="help-block">Specify name or username.</span>
                </div>
              </div>
            </fieldset>
          </form>
        </div>

        <div id="userList">
          <ul>
{$accessLevels=Access::getList()}
{foreach from=$globals_project->getUsers() item=projectUser}
{$userAccessLevel=$projectUser->getAccess()}
{$user=$projectUser->getPtrUser()}
            <li id="{$user->getUsername()}">
              <div class="avatar40"><img src="{$user->getAvatarUrl()}" width="40" height="40"></div>
              <div class="username"><h3>{$user->getUsername()}{if $user->getUsername()==$globals_user->getUsername()} <small>(this is you!)</small></h3>{/if}</div>
              <div class="actionItems">
{if !$globals_project->userHasAccessLevel($user, Access::OWNER)}
                <div class="remove"><a class="{$user->getUsername()} btn danger" href="{UrlManager::getForAjaxProjectRemoveUser()}">Remove</a></div>
                <div class="access"><a class="{$user->getUsername()} btn" href="#">Access</a></div>
                <div class="popover-wrapper">
                  <div id="accessLevelPaneLevels_{$user->getUsername()}" class="accessLevelPopover popover above">
                    <div class="arrow"></div>
                    <div class="inner">
                      <h3 class="title">Access level</h3>
                      <div class="content">
                        <ul class="inputs-list">
{foreach $accessLevels as $accessLevel => $accessName}
  {if $accessLevel !== 0} {* Don't show the NONE value access level *}
                          <li>
                            <input type="radio" value="{$user->getUsername()}_{$accessLevel}" name="accessLevel_{$user->getUsername()}" id="{$accessLevel}"{if $userAccessLevel == $accessLevel} checked{/if} />
                            <span>{$accessName|capitalize}</span>
                          </li>
  {/if}
{/foreach}
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
{else}
                <div class="noChanges">Owner (no changes allowed)</div>
{/if}
              </div>
            </li>
{/foreach}
          </ul>
        </div>
      </div>
{/if}
      <div id="integration">
        <fieldset>
{include file='includes/builderEditor.inc.tpl'}
        </fieldset>
      </div>

      <div id="release">
        <form action class="form" id="releaseForm">
          <fieldset>
            <div class="clearfix">
              <label>Generate packages?</label>
              <div class="input">
                <ul class="inputs-list">
                  <li>
                    <label>
                      <input type="checkbox" name="optionReleasePackage"{if $globals_project->getOptionReleasePackage()} checked="checked"{/if}>
                      <span class="help-block">A release package will be automatically generated after every successful build, and made available on the dashboard.</span>
                    </label>
                  </li>
                </ul>
              </div>
            </div>
            <div class="clearfix">
              <label for="releaseLabel" title="">Release label</label>
              <div class="input">
                <input class="span6" type="text" name="releaseLabel" value="{$globals_project->getReleaseLabel()}" />
                <span class="help-block">This will be used to name the release package files, suffixed by an internal incremental build number. e.g., on the 2154<sup>th</sup> project build the label "cintient-1.0.0" would generate the package "cintient-1.0.0-2154.tar.gz"</span>
              </div>
            </div>
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes" />&nbsp;<button type="reset" class="btn">Cancel</button>
            </div>
          </fieldset>
        </form>
      </div>

{*      <div id="deployment">
        <fieldset>
{include file='includes/builderEditor.inc.tpl'}
        </fieldset>
      </div>*}

    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  //
  // More complex stuff inside the init specific method
  //
  Cintient.initSectionProjectEdit({
    accessLevelPopoverSubmitUrl : '{UrlManager::getForAjaxProjectAccessLevelChange()}',
    userSearchSubmitUrl : '{UrlManager::getForAjaxSearchUser()}',
    addUserSubmitUrl : '{UrlManager::getForAjaxProjectAddUser()}',
  });
  //
  // Generic forms can use initGenericForm()
  //
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
  });
  Cintient.initGenericForm({
    formSelector : '#release form',
    submitUrl : '{UrlManager::getForAjaxProjectEditRelease()}',
  });
  //
  // The project avatar uploader
  //
	var uploader = new qq.FileUploader({
    element: document.getElementById('avatarUploader'),
    action: "{UrlManager::getForAjaxAvatarUpload(['p'=>1])}",
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
{include file='includes/footer.inc.tpl'}