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

*}
{$menuLinks="<a href=\"{UrlManager::getForProjectBuildHistory()}\">build history</a>"}
{if $globals_project->userHasAccessLevel($globals_user, Access::WRITE) || $globals_user->hasCos(UserCos::ROOT)}
  {$menuLinks="$menuLinks | <a href=\"{UrlManager::getForProjectEdit()}\">edit</a>"}
{/if}
{include file='includes/header.inc.tpl'
  subSectionTitle="Project"
  menuLinks=$menuLinks}
{include file='includes/projectHeader.inc.tpl' project=$globals_project project_latestBuild=$project_latestBuild}

<div class="whiteBoard">
<div id="projectLog">
{if !empty($project_log)}
<table>
{*  <thead>
    <tr>
      <th scope="col">date</th>
      <th scope="col">type</th>
      <th scope="col">message</th>
      <th scope="col">user</th>
    </tr>
  </thead>*}
  <tbody>
{foreach from=$project_log item=log}
{$currentDate=$log->getDate()|date_format:"%b %e, %Y"}
{if $currentDate != $lastDate}
    <tr>
      <th colspan="3">{$currentDate}</th>
    </tr>
{/if}
    <tr>
      <td>{$log->getDate()|date_format:"%R"}</td>
      {*<td>{$log->getType()}</td>*}
      <td>{$log->getMessage()}</td>
      <td>{$log->getUsername()}</td>
    </tr>
{$lastDate=$log->getDate()|date_format:"%b %e, %Y"}
{/foreach}
  </tbody>
</table>
{else}
Not a thing
{/if}
</div>
</div>

{include file='includes/footer.inc.tpl'}