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
{include file='includes/footer.inc.tpl'}