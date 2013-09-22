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

          <div class="row">
            <div class="span5 leftRow" id="builderElementsAvailable">
{function name=builderElement depth=0 context=''}
  {if is_array($element)}
    {if $depth!=0}
    {$key=key($element)}
                {*<li>*}
                  <li class="groupName"><span class="label">{if $key == 'Task'}Generic{elseif $key == 'Type'}Types{else}{$key}{/if}</span></li>
                  {*<ul class="builderElementDepth_{$depth}">*}
      {$element=current($element)}
    {/if}
    {* We have split the following into two foreachs, the first one deals
       with only the root elements (put into Generic) and the last one
       deals with grouped elements. This is a quick and easy fix for the
       problem with root tasks appearing after grouped ones. Case in
       point the ReplaceRegexp task, which appeared inside the Php group. *}
    {*foreach $element as $key => $value}
      {if is_array($value)}
        {$originalContext=$context}
        {$context="{$context}_$key"}
        {builderElement element=[$key => $value] depth=$depth+1 context=$context}
        {$context=$originalContext}
      {else}
                    <li class="task smoothHoverSmall"><a href="#" class="{$context}">{$value}</a></li>
      {/if}
    {/foreach*}
    {foreach $element as $key => $value}
      {if !is_array($value)}
                    <li class="task smoothHoverSmall"><a href="#" class="{$context}">{$value}</a></li>
        {$element[$key]=null}
      {/if}
    {/foreach}
    {foreach $element as $key => $value}
      {if is_array($value)}
        {$originalContext=$context}
        {$context="{$context}_$key"}
        {builderElement element=[$key => $value] depth=$depth+1 context=$context}
        {$context=$originalContext}
      {/if}
    {/foreach}
    {if $depth!=0}
                  {*</ul>
                </li>*}
    {/if}
  {else}
                <li><a href="#" class="{$context}">{$element}</a></li>
  {/if}
{/function}
              <ul class="builderElementDepth_0">
{builderElement element=$providerAvailableBuilderElements_elements}
              </ul>

            </div>

            <div class="span11 rightRow" id="builderElementsChosen">
              <ul id="sortable">
{$globals_project->getIntegrationBuilder()->toHtml()}
              </ul>
            </div>
          </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  //
  // Setup the details popovers
  //
  var activeTaskId = null
  $('.builderElementLine')
    .live('click', function (e) {
      // Un-highlight any active tasks
      if (activeTaskId != $(this).parent().prop('id')) {
        $('#' + activeTaskId + ' .builderElementPopover').hide();
        Cintient.deactivateListItem($('.builderElementLine', '#' + activeTaskId));
        activeTaskId = null;
      }
      if ($('+ .builderElementPopover', this).is(':visible')) {
        $('+ .builderElementPopover', this).hide();
        //Cintient.deactivateListItem($(this)); // redundant if we call hoverListItem()
        Cintient.hoverListItem($(this)); // it was clicking on, so it should be left hovered on
        activeTaskId = null;
      } else {
        Cintient.activateListItem($(this));
        $('+ .builderElementPopover', this).fadeIn(50);
        activeTaskId = $(this).parent().prop('id');
      }
      e.stopPropagation(); // So that it doesn't propagate into the document click handler below and closes the popover right after it opens
    })
    .live('hover', function (e) {
      if (e.type == 'mouseenter') {
        // Don't highlight the active item
        if (activeTaskId != $(this).parent().prop('id')) {
          Cintient.hoverListItem($(this));
        }
      } else if (e.type == 'mouseleave') {
        // Don't un-highlight the active item
        if (activeTaskId != $(this).parent().prop('id')) {
          Cintient.deactivateListItem($(this));
        }
      }
    })
  ;

  //
  // Close the popover on click anywhere on the page
  //
  $('.builderElementPopover').live('click', function (e) {
    // clicks inside the builder element don't close it.
    e.stopPropagation();
  });
  $(document).click(function() {
    if (activeTaskId != null) {
      $('#' + activeTaskId + ' .builderElementPopover').hide();
      Cintient.deactivateListItem($('#' + activeTaskId + ' .builderElementLine'));
      activeTaskId = false;
    }
  });

  //
	// Register click events for save links
  //
  $('#integration .builderElementPopover form').live('submit', function(e) {
    var that = this;
    var data = function() {
      var x = {};
      $('input', $(that)).each( function() {
        x[this.name] = { type: this.type, value: this.value };
      });
      $('textarea', $(that)).each( function() {
        x[this.name] = { type: this.type, value: this.value };
      });
      x['internalId'] = { type: 'hidden', value: $(that).parents('li').prop('id') };
      $('input:radio[name=type]:checked', $(that)).each( function() {
    	  x['type'] = { type: 'radio', value: $(this).val() }; // This overwrites the previous input iteration with the correct value for type
      });
      //
      // The following overwrites the previous "input" iteration and makes
      // sure that checkboxes have either 'on' or '' as sent values. Chrome
      // was always sending value as 'on' with the generic this.value used
      // in 'input' selector above.
      //
      $(':checkbox', $(that)).each( function() {
    	  x[this.name] = { type: 'checkbox', value: (this.checked == true ? 'on' : '') };
      });
      return x;
    }();
    $.ajax({
      url: '{UrlManager::getForAjaxProjectIntegrationBuilderSaveElement()}',
      data: data,
      type: 'POST',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (data == null || data.success == null) {
          Cintient.alertUnknown();
        } else if (!data.success) {
          Cintient.alertFailed(data.error);
        } else {
          $('input:submit', that).prop('value', 'Saved!');
          $('input:submit', that).prop('disabled', 'disabled');
          $('input:submit', that).removeClass('primary');
          Cintient.alertSuccess($('.builderElementLine h3', $(that).parents('li')).text() + " builder element saved.");
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        Cintient.alertUnknown();
      }
    });
    return false;
  });

  //
  // Show save links on change
  //
  $('#integration .builderElementPopover form').live('change keyup', function(e) {
    // TODO: This is a poor man's change detection system. Instead do a proper
    // hash of all inputs and check them for changes on keyup and change
    if (e.which == 9 || // TAB
       (e.which >=37 && e.which <=40) || // Cursor keys
       e.which == 16 || // SHIFT
       e.which == 91 || // Left Cmd
       e.which == 93 || // Right Cmd
       e.which == 18 || // Option (Alt)
       e.which == 17 || // CTRL
       e.which == 20 || // CAPS-LOCK
       e.which == 27 || // ESC
       e.which == 13) // Enter
    {
      return false;
    }
    $('input:submit', this).prop('value', 'Save changes');
    $('input:submit', this).prop('disabled', '');
    $('input:submit', this).addClass('primary');
  });
  //
	// Set up delete links
  //
  $('#integration .builderElementPopover form button.delete').live('click', function(e) {
    var that = this;
    $.ajax({
      url: '{UrlManager::getForAjaxProjectIntegrationBuilderDeleteElement()}',
      data: { internalId: $(this).parents('li').prop('id') },
      type: 'POST',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (data == null || data.success == null) {
          Cintient.alertUnknown();
        } else if (!data.success) {
          Cintient.alertFailed(data.error);
        } else {
          $(that).parents('li').fadeOut(500);
          setTimeout(
            function() {
              $(that).parents('li').remove();
            },
            450 // Slightly faster than the fadeOut, so that the next items get pulled up before the element fades first
          );
          Cintient.alertSuccess($('.builderElementLine h3', $(that).parents('li')).text() + " builder element deleted.");
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        Cintient.alertUnknown();
      }
    });
    // Apparently form submit is being triggered by this handler (?),
    // so this should stop that (since e.stopPropagation() doesn't quite
    // work with live(). 1.7's on() method should also right this wrong
    return false;
  });

  //
  // Set up add links
  //
  $('#builderElementsAvailable li.task').click(function(e) {
    // We're catching the li instead of the a, so that we can also click
    // in all the element, not should the text itself.
    e.preventDefault();
    $.ajax({
      url: '{UrlManager::getForAjaxProjectIntegrationBuilderAddElement()}',
      data: { task: $('a', this).text(), parent: $('a', this).attr('class') },
      type: 'POST',
      cache: false,
      dataType: 'html',
      success: function(data, textStatus, XMLHttpRequest) {
        if (data == null) {
          Cintient.alertUnknown();
        } else {
          $('#builderElementsChosen > ul').append(data);
          //Cintient.activateListItem($('#builderElementsChosen > ul > li:last .builderElementLine'));
          // According to:
          // http://api.jquery.com/animate/
          // ... the background-color cannot be animated unless the jQuery.Color()
          // plugin is used. This stays here as a reminder.
          /*$('.builderElementLine', $('#builderElementsChosen > ul > li:last')).animate({
            backgroundColor : '#fff'
          }, 5000);*/
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        Cintient.alertUnknown();
      }
    });
  });

  //
  // Setup sorting
  //
  var builderElementsChosen = $('#sortable');

  // Enable sorting
  builderElementsChosen.sortable(
  {
    axis: 'y',
    cursor: 'move',
    //cursorAt: 'top',
    disabled: false,
    distance: 20,
    //items: '.builderElement',
    opacity: 0.6,
    //placeholder: 'ui-state-highlight',
    //revert: 100,
    scroll: true,
    stop: function(event, ui) {
  	  var newSort = builderElementsChosen.sortable('toArray');
      if (newSort.join('') != initialSort.join('')) { // is toString() equally ubiquous?
      	$.ajax({
  	      url: '{UrlManager::getForAjaxProjectIntegrationBuilderSortElements()}',
  	      data: { sortedElements: newSort },
  	      type: 'POST',
  	      cache: false,
  	      dataType: 'json',
  	      success: function(data, textStatus, XMLHttpRequest) {
  	        if (data == null || data.success == null) {
  	          Cintient.alertUnknown();
  	        } else if (!data.success) {
  	          Cintient.alertFailed(data.error);
  	        } else {
  	          initialSort = newSort;
  	          Cintient.alertSuccess('Successfully rearranged the order of your builder elements.');
  	        }
  	      },
  	      error: function(XMLHttpRequest, textStatus, errorThrown) {
  	        Cintient.alertUnknown();
  	      }
  	    });
      }
   	},
    tolerance: 'pointer',
  });

  // Get initial sort in order to detect changes
  initialSort = builderElementsChosen.sortable('toArray');
});
//]]>
</script>
