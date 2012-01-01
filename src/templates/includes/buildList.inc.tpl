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
{if !empty($project_build)}
      <div id="buildListDropdownLink">
        <div class="dropdownArrowDark"></div><span id="activeRevision"><span class="label {if $project_build->getStatus()!=Project_Build::STATUS_FAIL}success{else}important{/if}">{if $project_build->getStatus()!=Project_Build::STATUS_FAIL}Ok{else}Failed{/if}</span>
        #{$project_build->getId()}, rev {$project_build->getScmRevision()},
        on {$project_build->getDate()|date_format:"%b %e, %Y at %R"}</span>
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
            <div class="row smoothHoverSmall" id="{$build->getId()}">
              <div class="span1 dataBuildStatus"><span class="label {if $build->getStatus()!=Project_Build::STATUS_FAIL}success{else}important{/if}">{if $build->getStatus()!=Project_Build::STATUS_FAIL}Ok{else}Failed{/if}</span></div>
              <div class="span1 dataBuildStartDate">{$build->getDate()|date_format:"%R"}</div>
              <div class="span1 dataBuildNum">#{$build->getId()}</div>
              <div class="span2 dataBuildRev"><span class="{$build->getScmRevision()}">{$build->getScmRevision()|truncate:8:''}</span></div>
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
    // Make the menu promptly disappear, don't set buildListActive to
    // false yet, so that another click in the dropdown doesn't make the
    // menu appear again while the ajax request is not complete. After
    // the ajax response comes, make buildListActive false again.
    $('#buildList').fadeOut(50);
    //
    // Update the visible active revision
    //
    $('#activeRevision').html(
      $('.dataBuildStatus', this).html() + ' ' +
      $('.dataBuildNum', this).html() + ', rev ' +
      $('.dataBuildRev span', this).prop('class') + ', on ' +
      $('.dataBuildStartDate', this).html()
    );

    activeTabId = $('.tab-content .active').attr('id'); // Fetch the currently active id before it goes away
    //
    // The source of a very likely huge future bug... Sometimes the
    // activeTabId fetching comes out undefined, not really sure
    // why at this time. Always having a default tab to activate
    // is right now the best course of action to take.
    //
    if (typeof activeTabId == 'undefined') {
      activeTabId = 'rawOutput';
    }
    // Promptly hide the content
    // TODO: show a waiting indicator
    $('#buildHistoryRev').hide();
    $('#buildHistoryRev').html('<div class="loading"><img src="imgs/loading-3.gif" /></div>');
    $('#buildHistoryRev').show();
    $.ajax({
      url: '{UrlManager::getForAjaxProjectBuildHistory()}',
      data: { pid : {$globals_project->getId()}, bid : $(this).prop('id') },
      type: 'GET',
      cache: true,
      dataType: 'html',
      success: function(data, textStatus, XMLHttpRequest) {
        // Following condition according to jQuery's .load() method
        // documentation:
        // http://api.jquery.com/load/
        if (textStatus == 'success' || textStatus == 'notmodified') {
          $('#buildHistoryRev')
            .hide()
            .html(data); // Update the HTML (replace it)
          $('ul.tabs > li.active').removeClass('active'); // Throw away the HTML forced active tab
          $('.tab-content .active').removeClass('active'); // Throw away the HTML forced active content
          $('ul.tabs > li a[href="#' + activeTabId + '"]').parent().addClass('active'); // Honor the previously user active tab
          $('.tab-content #' + activeTabId).addClass('active'); // Honor the previously user active content
          $('.tabs').tabs(); // Init the Bootstrap tabs
          $('#buildHistoryRev').fadeIn(300); // Show it all
        } else {
          alertUnknown();
        }
        buildListActive = false;
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alertUnknown();
        buildListActive = false;
      }
    });
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