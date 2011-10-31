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
  subSectionId="settings"
  subSectionTitle="Settings"
  jsIncludes=['js/lib/avataruploader.js',
              'js/lib/bootstrap/bootstrap-tabs.js']
  cssIncludes=['css/lib/avataruploader.css']}

    <ul class="tabs">
      <li class="active"><a href="#general">General</a></li>
      <li><a href="#notifications">Notifications</a></li>
    </ul>

    <div class="tab-content">
      <div class="active" id="general">
        <form action class="form" id="generalForm">
          <fieldset>
            <div class="clearfix">
              <label for="title">Your avatar</label>
              <div id="avatarUploader">
                <noscript>
                  {* TODO: Use simple upload form *}
                  <p>Please enable JavaScript to use file uploader.</p>
                </noscript>
              </div>
              <span class="help-inline">Click image to change it.</span>
            </div>
          </fieldset>
        </form>
      </div>
      <div id="notifications">
        <form action class="form" id="notificationsForm">
          <fieldset>
{foreach $globals_user->getNotifications() as $notification}
            <div id="{$notification->getHandler()}" class="notificationHandler">{* Required to diferentiate between possible future handlers *}
              <h3>{substr($notification->getHandler(), 13)}</h3>
{$notification->getView()}
            </div>
{* TODO: separate notification handlers with CSS border line *}
{/foreach}
            <div class="actions">
              <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionSettings();
  Cintient.initGenericForm({
    formSelector : 'form#notificationsForm .notificationHandler',
    submitUrl: '{URLManager::getForAjaxSettingsNotificationsSave()}',
  });
});
</script>
<style type="text/css">
.qq-upload-button
{
  background-image: url({$globals_user->getAvatarUrl()});
}
</style>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	var uploader = new qq.FileUploader({
    element: document.getElementById('avatarUploader'),
    action: '{UrlManager::getForAjaxAvatarUpload()}',
    multiple: false,
    allowedExtensions: ['jpg', 'jpeg', 'png'],
    sizeLimit: {$smarty.const.CINTIENT_AVATAR_MAX_SIZE},
    onSubmit: function(id, fileName){

    },
    onComplete: function(id, fileName, responseJSON) {
      // Update all the avatars on the current page
      $("#avatarImg").attr('src', responseJSON.url);
      $(".qq-upload-button").css({
        'background-image' : 'url(' + responseJSON.url + ')'
      });
    }
  });
});
//]]>
</script>
{include file='includes/footer.inc.tpl'}