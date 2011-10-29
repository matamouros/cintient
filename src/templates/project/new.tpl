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
subSectionTitle="New project"
subSectionId="projectNew"
level=1}
      <form action class="form" id="newProjectContainer">
        <fieldset>
          <div class="clearfix">
            <label for="title">Project title</label>
            <div class="input">
              <input class="span7" type="text" name="title" value="{if isset($formData['title'])}{$formData['title']}{/if}">
            </div>
          </div>
          <div class="clearfix">
            <label for="buildLabel" class="tooltip" title="This will be used to name the release package files.">A build label</label>
            <div class="input">
              <input class="span6" type="text" name="buildLabel" value="{if isset($formData['buildLabel'])}{$formData['buildLabel']}{/if}">
            </div>
          </div>
          <div class="clearfix">
            <label for="scmConnectorType">The SCM connector</label>
            <div class="input">
              <select class="span2" name="scmConnectorType">
{foreach from=$project_availableConnectors item=connector}
                <option value="{$connector}"{if isset($formData['scmConnectorType']) && $formData['scmConnectorType']==$connector} selected{/if}>{$connector|capitalize}
{/foreach}
              </select>
            </div>
          </div>
          <div class="clearfix">
            <label for="scmRemoteRepository">The SCM remote repository</label>
            <div class="input">
              <input class="span10" type="text" name="scmRemoteRepository" value="{if isset($formData['scmRemoteRepository'])}{$formData['scmRemoteRepository']}{/if}">
            </div>
          </div>
          <div class="clearfix">
            <label for="scmUsername">Username for SCM access</label>
            <div class="input">
              <input class="span6" type="text" name="scmUsername" value="{if isset($formData['scmUsername'])}{$formData['scmUsername']}{/if}">
              <span class="help-inline">This field is optional.</span>
            </div>
          </div>
          <div class="clearfix">
            <label for="scmPassword">Password for SCM access</label>
            <div class="input">
              <input class="span6" type="text" name="scmPassword" value="{if isset($formData['scmPassword'])}{$formData['scmPassword']}{/if}">
              <span class="help-inline">This field is optional.</span>
            </div>
          </div>
          <div class="actions">
            <input type="submit" class="btn primary" value="Save changes">&nbsp;<button type="reset" class="btn">Cancel</button>
          </div>
        </fieldset>
      </form>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initGenericForm({
    formSelector : '#projectNew form',
    onSuccessRedirectUrl : '{UrlManager::getForDashboard()}',
    submitUrl: '{UrlManager::getForAjaxProjectNew()}',
  });
});
</script>
{include file='includes/footer.inc.tpl'}