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
{if !empty($project_build)}
      <div id="buildListDropdownLink">
        <div class="dropdownArrowDark"></div><span class="label {if $project_build->getStatus()!=Project_Build::STATUS_FAIL}success{else}important{/if}">{if $project_build->getStatus()!=Project_Build::STATUS_FAIL}Ok{else}Failed{/if}</span>
        #{$project_build->getId()}, rev {$project_build->getScmRevision()},
        on {$project_build->getDate()|date_format:"%b %e, %Y at %R"}
      </div>


      <div id="buildList" class="popover below">
        <div class="arrow"></div>
        <div class="inner">
          <h3 class="title">Choose a different build</h3>
          <div class="content">


{if !empty($project_buildList)}
<div class="tablex">
{foreach from=$project_buildList item=build}
{$currentDate=$build->getDate()|date_format:"%B %e, %Y"}
{if $currentDate != $lastDate}
            <div class="row date">
              <div class="span5"><h5>{$currentDate}</h5></div>
            </div>
{/if}
              <div class="row smoothHoverSmall"><a href="{UrlManager::getForProjectBuildView($globals_project, $build)}">
                <div class="span1"><span class="label {if $build->getStatus()!=Project_Build::STATUS_FAIL}success{else}important{/if}">{if $build->getStatus()!=Project_Build::STATUS_FAIL}Ok{else}Failed{/if}</span></div>
                <div class="span1">{$build->getDate()|date_format:"%R"}</div>
                <div class="span1">#{$build->getId()}</div>
                <div class="span2">{$build->getScmRevision()|truncate:8:''}</div>
                </a>
              </div>

{$lastDate=$build->getDate()|date_format:"%B %e, %Y"}
{/foreach}
</div>
        </div>

<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
  //
  // The build list dropdown
  //
  buildListActive = false;
  $('#buildListDropdownLink').click( function(e) {
    if (buildListActive) {
    	$('#buildList').fadeOut(50);
    } else {
      $('#buildList').fadeIn(50);
    }
    buildListActive = !buildListActive;
    e.stopPropagation();
  });
  //
  // Bind li hovering to click (can't get around to have anchors in there
  // and still make it look good)
  //
  $('#buildList .row').click(function (e) {
    e.preventDefault();
    window.location = $('a', this).prop('href');
  });
  //
  // Close any menus on click anywhere on the page
  //
  $(document).click(function(){
    if (buildListActive) {
      $('#buildList').fadeOut(50);
      buildListActive = false;
    }
  });
});
// ]]>
</script>
{/if}
</div>

      </div>
{* else "you have no builds yet" *}
{/if}