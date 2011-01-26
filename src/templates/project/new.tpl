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

*}{include file='includes/header.inc.tpl' menuLeft="New project"}
    <form action="{URLManager::getForProjectNew()}" method="post">
    <div id="newProjectContainer" class="container">
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
      <input type="submit" value="Create!" id="submitButton">
    </div>
    </form>
    </div>
{include file='includes/footer.inc.tpl'}