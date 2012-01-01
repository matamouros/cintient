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

*}
{* These two captures insure initialization, so that $smarty.capture  *}
{* array always exists for these vars, even in case no special tasks  *}
{* were defined for the current build.                                *}
{capture name="specialTaskLink"}{/capture}
{capture name="specialTaskPane"}{/capture}
{$specialTaskPanes=array()}
{foreach $project_specialTasks as $task}
  {include file="includes/specialTask/$task.inc.tpl"}
  {$specialTaskLink[]=$smarty.capture.specialTaskLink}
  {* We should be using capture append for specialTaskPane, but apparently *}
  {* it always only holds the last value... Come back to this later.       *}
  {$specialTaskPanes[]=$smarty.capture.specialTaskPane}
{/foreach}
    <ul class="tabs">
      <li class="active"><a href="#rawOutput">Raw output</a></li>
{foreach $specialTaskLink as $link}
      {$link}
{/foreach}
    </ul>

    <div class="tab-content">
      <div class="active" id="rawOutput"><div class="log">{$project_build->getOutput()|raw2html}</div></div>
{foreach $specialTaskPanes as $taskPane}
{$taskPane}
{/foreach}
    </div>