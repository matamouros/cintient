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
  subSectionTitle="Settings"}
    <div id="settingsContainer" class="container">
      <ul>
        <li class="settingsNode">
          <div class="label">Change your avatar</div>
          <div id="avatarUploader">
            <noscript>
              {* TODO: Use simple upload form *}
              <p>Please enable JavaScript to use file uploader.</p>
              <!-- or put a simple form for upload here -->
            </noscript>
          </div>
        </li>
        <li class="settingsNode">
          <div class="label">Notification emails <span class="fineprintLabel">(2 max. More will be ignored)</span></div>
          <div class="textareaContainer">
            <textarea class="textarea" name="notificationEmails"></textarea>
          </div>
        </li>
      </ul>
    </div>
<style type="text/css">
.qq-upload-button
{
  background-image: url({$smarty.session.user->getAvatarUrl()});
}
</style>
<script type="text/javascript" src="/js/avataruploader.js"></script>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
	var uploader = new qq.FileUploader({
    element: document.getElementById('avatarUploader'),
    action: '{URLManager::getForAjaxAvatarUpload()}',
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