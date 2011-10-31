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
            <ul class="tabs">
              <li class="active"><a href="#general">General</a></li>
              <li><a href="#charts">Charts</a></li>
              <li><a href="#log">Log</a></li>
              <li><a href="#releases">Releases</a></li>
            </ul>

            <div class="tab-content">
              <div class="active" id="general">
                <div class="row">
                  <div class="span2">Current status:</div>
                  <div class="span6"><span class="label {if $project->getStatus()==Project::STATUS_OK}success{elseif $project->getStatus()==Project::STATUS_BUILDING}notice{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}warning{else}important{/if}">{if $project->getStatus()==Project::STATUS_OK}Ok{elseif $project->getStatus()==Project::STATUS_BUILDING}Building{elseif $project->getStatus()==Project::STATUS_UNINITIALIZED}Uninitialized{else}Failed{/if}</span></div>
                </div>
                <div class="row">
                  <div class="span2">Latest build:</div>
                  <div class="span6">#{$project_build->getId()}</div>
                </div>
                <div class="row">
                  <div class="span2">Commit:</div>
                  <div class="span6">{$project_build->getScmRevision()}</div>
                </div>
                <div class="row">
                  <div class="span2">Finished:</div>
                  <div class="span6">{Utility::timeDurationToHumanReadable(time()-strtotime($project_build->getDate()), 'yMdwhm')}</div>
                </div>
                {*<div class="row">
                  <div class="span2">Duration:</div>
                  <div class="span3">3 min 56 sec</div>
                </div>*}
              </div>
              <div id="charts">
                <ul class="media-grid">
                  <li>
                    <div id="chartBuildOutcomesContainer" class="chart" style="display: none;"></div>
                  </li>
                  <li>
                    <div id="chartBuildTimelineContainer" class="chart" style="display: none;"></div>
                  </li>
                </ul>
              </div>
              <div id="log">
{if !empty($project_log)}
                <table class="zebra-striped">
                  <thead>
                    <tr>
                      <th class="header yellow headerSortDown">Datetime</th>
                      <th class="header blue">Message</th>
                      <th class="header green">User</th>
                    </tr>
                  </thead>
                  <tbody>
{foreach from=$project_log item=log}
                    <tr>
                      <td>{$log->getDate()|date_format:"%b %e, %Y - %R"}</td>
                      <td>{$log->getMessage()}</td>
                      <td>{if $log->getUsername() == ''}Cintient{else}$log->getUsername(){/if}</td>
                    </tr>
{/foreach}
                  </tbody>
                </table>
{/if}
              </div>
              <div id="releases">
                Soon!
              </div>
            </div>

<script type="text/javascript">
// <![CDATA[
var chartBuildOutcomes;
var chartBuildTimeline;
$(document).ready(function() {
  //
  // Build outcomes
  //
	chartBuildOutcomes = new Highcharts.Chart({
    chart: {
      renderTo: 'chartBuildOutcomesContainer',
      type: 'pie'
    },
    title: {
      text: 'Build outcomes'
    },
    tooltip: {
      formatter: function() {
        slice = Math.round(this.y * 100 / {math equation="x+y" x=$project_buildStats.buildOutcomes.0 y=$project_buildStats.buildOutcomes.1});
        return this.point.name +': '+ slice + '%';
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: false,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          formatter: function() {
            return this.point.name +': '+ this.y;
          }
        }
      }
    },
    series: [{
      type: 'pie',
      name: 'Build outcomes',
      data: [
        ['Ok', {$project_buildStats.buildOutcomes.1}],
        {
          name: 'Failed',
          y: {$project_buildStats.buildOutcomes.0},
          sliced: true,
          selected: true
        }
      ]
    }]
  });
  $('#chartBuildOutcomesContainer').fadeIn(600);

  //
  // Build timeline
  //
  chartBuildTimeline = new Highcharts.Chart({
    chart: {
      renderTo: 'chartBuildTimelineContainer',
      defaultSeriesType: 'scatter',
      zoomType: 'xy',
    },
    title: {
      text: 'Build timeline'
    },
    subtitle: {
      text: ''
    },
    xAxis: {
      type: 'datetime',
      title: {
        text: ''
      },
      maxZoom: 5 * 24 * 3600000, // 5 days (minimum to not breakdown from days into hours on the XX axis)
    },
    yAxis: {
      title: {
        text: ''
      },
      maxZoom: 1 * 24 * 3600000, // 1 day
      labels: {
        formatter: function() {
          if (this.value > 999) { // beats to hours
            hours = 24;
          } else {
            yDate = new Date(this.value*86.4*1000);
            hours = yDate.getHours();
          }
          return '' + hours + 'h';
        }
      },
      min: 0,
      max: 1000,
    },
    tooltip: {
      formatter: function() {
        date = new Date(this.x);
        return '' + date.toDateString() + ', ' + date.toTimeString();
      }
    },
    legend: {
      layout: 'vertical',
      align: 'left',
      verticalAlign: 'top',
      x: 30,
      y: 190,
      floating: true,
      backgroundColor: {
        linearGradient: [0, 0, 0, 50],
        stops: [
          [0, 'rgba(96, 96, 96, .1)'],
          [1, 'rgba(16, 16, 16, .1)']
        ]
      },
      borderWidth: 1
    },
    plotOptions: {
      scatter: {
        marker: {
          radius: 5,
          states: {
            hover: {
              enabled: true,
            }
          }
        },
        states: {
          hover: {
            marker: {
              enabled: false
            }
          }
        }
      }
    },
    series: [
      {
        type: 'scatter',
        name: 'Ok',
        color: 'rgba(124,196,0, .4)',
        //color: 'rgba(70,165,70,.4)',
        data: [
{foreach from=$project_buildStats.buildTimeline.ok item=ok}
{if !$ok@first}
,
{/if}
{$okMilli=$ok*1000}
[{$okMilli}, {$ok|date_format:"B"}]
{/foreach}
      ]},
      {
        name: 'Failed',
        color: 'rgba(255,40,0, .4)',
        //color: 'rgba(196,60,53,.4)',
        data: [
{foreach from=$project_buildStats.buildTimeline.failed item=failed}
{if !$failed@first}
,
{/if}
{$failedMilli=$failed*1000}
[{$failedMilli}, {$failed|date_format:"B"}]
{/foreach}
        ]
      }
    ]
  });
  $('#chartBuildTimelineContainer').fadeIn(600);
});

// ]]>
</script>