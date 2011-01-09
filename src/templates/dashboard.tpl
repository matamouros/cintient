{include file='includes/header.inc.tpl'}
<a href="{URLManager::getForProjectNew()}">new project</a>
<br>
<br>
{foreach $dashboard_projectList as $project}
  <a href="{URLManager::getForProjectView($project)}">{$project->getTitle()}</a> status: {$project->getStatus()}
  <br>
{foreachelse}
N&atilde;o tem projectos.
{/foreach}
{include file='includes/footer.inc.tpl'}