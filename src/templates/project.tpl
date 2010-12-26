{include file='includes/header.inc.tpl'}
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
  build #{$build->getId()}
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