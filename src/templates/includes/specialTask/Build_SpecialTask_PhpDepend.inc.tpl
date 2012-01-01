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
{capture name="specialTaskLink"}<li><a href="#quality">Quality</a></li>{/capture}
{capture name="specialTaskPane"}
      <div id="quality">
{if !isset($project_jdependChartFilename) && !isset($project_overviewPyramidFilename)}
No quality metrics were collected in this build. If you haven't enabled
this yet, please add a PHP_Depend task to this project's integration
builder, and configure it properly. If you already have this task enabled,
please check the raw output of this build for problems, such as a PHP Fatal error.
{else}
        <ul class="media-grid">
          <li><div id="jdependChart" class="chart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_overviewPyramidFilename, ['pid' => $project_build->getProjectId(), 'bid' => $project_build->getId()])}" width="392" height="270" /></div></li>
          <li><div id="overviewChart" class="chart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_jdependChartFilename, ['pid' => $project_build->getProjectId(), 'bid' => $project_build->getId()])}" width="392" height="270" /></div></li>
        </ul>
{/if}
      </div>
{/capture}