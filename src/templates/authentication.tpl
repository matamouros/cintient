{*
    Cintient, Continuous Integration made simple.
    Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>

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
subSectionTitle="Authentication"
subSectionId='authentication'
subSectionDescription="log in with your username and password"
jsIncludes=[]
cssIncludes=[]}
    <form action class="form" id="authenticationForm" style="display:none;">
      <fieldset>
        <div class="clearfix">
          <label for="username">Username</label>
          <div class="input">
            <input class="span3 autofocus" type="text" name="username" autofocus />
          </div>
        </div>
        <div class="clearfix">
          <label for="password">Password</label>
          <div class="input">
            <input class="span3" type="password" name="password" />
          </div>
        </div>
        <div class="actions">
          <input type="submit" class="btn primary" value="Go!" />{if $globals_settings[SystemSettings::ALLOW_USER_REGISTRATION]}&nbsp;<a href="{UrlManager::getForRegistration()}" class="btn">Register</a>{/if}
        </div>
      </fieldset>
    </form>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionAuthentication();
  Cintient.initGenericForm({
    onSuccessRedirectUrl : window.location.href,
    submitUrl : '{UrlManager::getForAjaxAuthentication()}'
  });
});
// ]]>
</script>
{include file='includes/footer.inc.tpl'}