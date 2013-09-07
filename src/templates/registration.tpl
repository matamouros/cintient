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
subSectionTitle="Registration"
subSectionId="registration"
subSectionDescription="create a new user"
jsIncludes=[]
cssIncludes=[]}
    <form action class="form" id="registrationForm">
      <fieldset>
        <div class="clearfix">
          <label for="name">Name</label>
          <div class="input">
            <input class="span5 autofocus" type="text" name="name" autofocus />
          </div>
        </div>
        <div class="clearfix">
          <label for="email">Email</label>
          <div class="input">
            <input class="span6" type="text" name="email" />
          </div>
        </div>
        <div class="clearfix">
          <label for="username">Username</label>
          <div class="input">
            <input class="span4" type="text" name="username" />
          </div>
        </div>
        <div class="clearfix">
          <label for="password">Password</label>
          <div class="input">
            <input class="span3" type="password" name="password" />
          </div>
        </div>
        <div class="clearfix">
          <label for="password2">Repeat password</label>
          <div class="input">
            <input class="span3" type="password" name="password2" />
          </div>
        </div>
        <div class="actions">
          <input type="submit" class="btn primary" value="Go!">&nbsp;<a href="{UrlManager::getForAuthentication()}" class="btn">Cancel</a>
        </div>
      </fieldset>
    </form>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initGenericForm({
    onSuccessRedirectUrl : '{UrlManager::getForAuthentication()}',
    submitUrl : '{UrlManager::getForAjaxRegistration()}'
  });
});
// ]]>
</script>
{include file='includes/footer.inc.tpl'}