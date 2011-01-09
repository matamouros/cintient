{*
  Cintient, Continuous Integration made simple.
  
  Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  
  . Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
  
  . Redistributions in binary form must reproduce the above
    copyright notice, this list of conditions and the following
    disclaimer in the documentation and/or other materials provided
    with the distribution.
    
  . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
    the names of its contributors may be used to endorse or promote
    products derived from this software without specific prior
    written permission.
    
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
  
*}{include file='includes/header.inc.tpl'}
{if isset($smarty.get.new)}
{* PROJECT NEW *}
<form action="{URLManager::getForProjectNew()}" method="post">
Title <input type="text" name="title">
<br>
Description <textarea rows="3" cols="30" name="description"></textarea>
<br>
Build label <input type="text" name="buildLabel">
<br>
Connector
<select name="scmConnectorType">
{foreach from=$project_availableConnectors item=connector}
  <option value="{$connector}">{$connector}
{/foreach}
</select>
<br>
SCM Username <input type="text" name="scmUsername">
<br>
SCM Password <input type="password" name="scmPassword">
<br>
SCM Remote Repository <input type="text" name="scmRemoteRepository">
<br>
<input type="submit">
</form>
{else}
{* PROJECT DETAILS *}
Title: {$smarty.session.project->getTitle()}
<br>
Build label: {$smarty.session.project->getBuildLabel()}
<br>
Status: {$smarty.session.project->getStatus()}
<br>
Description: {$smarty.session.project->getDescription()}
<br>
Connector: {$smarty.session.project->getScmConnectorType()}
<br>
SCM Username: {$smarty.session.project->getScmUsername()}
<br>
SCM Password: {*$smarty.session.project->getScmUsername()*}
<br><br>
<a href="{URLManager::getForProjectBuild($smarty.session.project)}">Build!!</a>
<br>
<a href="{URLManager::getForProjectEdit($smarty.session.project)}">clique aqui para editar</a>
<br><br>
<b>Builds</b>
{foreach from=$project_buildList item=build}
  <br><br>
  <a href="{URLManager::getForProjectBuildView($build->getId())}">build #{$build->getId()}</a>
  <br>
  status: {$build->getStatus()}
  <br>
  release file: {if $build->getStatus() == ProjectBuild::STATUS_OK_WITH_PACKAGE}<a href="#">package_file</a> (<a href="#">re-generate</a>){else}<a href="#">generate</a>{/if}
  <br>
  output:
  {if $build->getOutput()}
  <textarea cols="120" rows="10">{$build->getOutput()}</textarea>
  {/if}
  <a href="#">(details)</a>
{/foreach}
{/if}
{include file='includes/footer.inc.tpl'}