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

*}{$defaultPane="#notificationsPane"}
{$menuLinks="<a href=\"#\" class=\"generalPane\">general</a> | <a href=\"#\" class=\"notificationsPane\">notifications</a>"}
{include file='includes/header.inc.tpl'
  subSectionTitle="Settings"
  menuLinks="<span id=\"exclusivePaneLinks\">$menuLinks</span>"
  jsIncludes=['js/lib/avataruploader.js']}
    <div id="paneContainer">
      <div id="generalPane" class="exclusivePane">
        <ul>
          <li class="settingsNode">
            <div class="label">Change your avatar</div>
            <div id="avatarUploader">
              <noscript>
                {* TODO: Use simple upload form *}
                <p>Please enable JavaScript to use file uploader.</p>
              </noscript>
            </div>
          </li>
        </ul>
      </div>
      <div id="notificationsPane" class="exclusivePane">
{foreach $globals_user->getNotifications() as $notification}
        <div id="{$notification->getHandler()}" class="projectEditContainer container">
          <div>{$notification->getHandler()}</div>
{$notification->getView()}
        </div>
{if !$notification@last}
        <hr />
{/if}
{/foreach}
      </div>
    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  cintient.initExclusivePanes('{$defaultPane}');
  cintient.initGenericForm({
    formSelector : '#notificationsPane .projectEditContainer',
    submitButtonAppendTo : '#notificationsPane',
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