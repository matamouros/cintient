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
{include file='includes/header.inc.tpl'
	subSectionId="projectBuildHistory"
  subSectionDescription="build history"
	subSectionTitle=$globals_project->getTitle()
  subSectionImg=$globals_project->getAvatarUrl()
  subSectionInclude="includes/buildList.inc.tpl"
  backLink="{UrlManager::getForProjectView()}"
	jsIncludes=['js/lib/highcharts.js',
              'js/lib/cintientHighcharts.theme.js',
              'js/cintientHighcharts.js',
							'js/lib/bootstrap/bootstrap-tabs.js']}
{if !empty($project_buildList)}
    <div id="buildHistoryRev">
{include file='includes/buildHistoryRev.inc.tpl'}
    </div>
{else}
    <div>This project has never been built. Come back later.</div>
{/if}
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  Cintient.initSectionBuildHistory();
});
// ]]>
</script>
{include file='includes/footer.inc.tpl'}