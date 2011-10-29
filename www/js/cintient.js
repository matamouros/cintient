/*
 * 
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca
 *
 *  This file is part of Cintient.
 *
 *  Cintient is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Cintient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Cintient. If not, see <http://www.gnu.org/licenses/>.
 *  
 */

/**
 * This is Cintient's JS helper class. It implements a singleton pattern,
 * by just using a simple object literal and assigning to Cintient var.
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 */
var Cintient = {
  /**
   * Creates a button, styles it and returns it. Whomever calls this,
   * must then be responsible for appending it to the DOM tree.
   */
  createButton: function (text)
  {
    var button = document.createElement('button');
    //
    // Style it
    //
    $(button).each( function() {
      $(this).hover(
        function() {
          $(this).css({
            "cursor" : "pointer",
            "border" : "2px solid rgb(255,40,0)",
            "box-shadow" : "0px 0px 20px rgb(255,40,0)",
            "-webkit-box-shadow" : "rgb(255,40,0) 0px 0px 20px",
            "-moz-box-shadow" : "rgb(255,40,0) 0px 0px 15px"
          });
        },
        function() {
          $(this).css({
            "cursor" : "default",
            "border" : "2px solid #999",
            "box-shadow" : "2px 2px 10px #111",
            "-webkit-box-shadow" : "#111 2px 2px 10px",
            "-moz-box-shadow" : "#111 2px 2px 10px"
          });
        });
      }
    );
    $(button).html("<div>" + text + "</div>");
    return button;
  },
  
  initSectionDashboard: function ()
  {
    var options = $.extend({}, arguments[0] || {});
    
    function activate(elem)
    {
      elem.css({
        "background-color" : "rgba(0, 67, 138, 0.1)" // Active color is stronger than hover
      });
    }
    
    function deactivate(elem)
    {
      elem.css({
        "cursor" : "default",
        "background-color" : "#fff",
      });
    }
    
    function hover(elem)
    {
      elem.css({
        "cursor" : "pointer",
        "background-color" : "rgb(248, 248, 248)"
      });
    }
    
    // Activate the default project
    activate($('#dashboard li.project#' + activeProjectId));
    
    //
    // Hover & click on the project list
    //
    $('#dashboard li.project')
      .click(function(e) {
        //
        // Engage only if an unactive project was clicked
        //
        if ($(this).attr('id') != activeProjectId) {
          activate($(this)); // Visual clues: promptly activate the clicked project
          deactivate($('#dashboard li.project#' + activeProjectId)); // ... then deactivate the previously active project          
          activeProjectId = $(this).attr('id'); // Update the active project
          
          // Promptly hide the content
          // TODO: show a waiting indicator
          $('#dashboard #dashboardProject').hide();
          
          $.ajax({
            url: options.submitUrl,
            data: { projectId : $(this).attr('id') },
            type: 'GET',
            cache: true,
            dataType: 'html',
            success: function(data, textStatus, XMLHttpRequest) {
              // Following condition according to jQuery's .load() method
              // documentation:
              // http://api.jquery.com/load/
              if (textStatus == 'success' || textStatus == 'notmodified') {
                var activeId = $('.pill-content .active').attr('id'); // Fetch the currently active id before it goes away
                $('#dashboard #dashboardProject').html(data); // Update the HTML (replace it)
                $('ul.tabs > li.active').removeClass('active'); // Throw away the HTML forced active tab
                $('.pill-content .active').removeClass('active'); // Throw away the HTML forced active content
                $('ul.tabs > li a[href="#' + activeId + '"]').parent().addClass('active'); // Honor the previously user active tab
                $('.pill-content #' + activeId).addClass('active'); // Honor the previously user active content
                $('.tabs').tabs(); // Init the Bootstrap tabs
                $("#log table").tablesorter({ sortList: [[0,1]] }); // Init the project log table sorter, sort the first column, DESC
                $('#dashboard #dashboardProject').fadeIn(300); // Show it all
              } else {
                $.jGrowl('An unknown error occurred. Yeah, it seems we have those too... :-(', { header: "Warning", sticky: true });
              }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              $.jGrowl('An unknown error occurred. Yeah, it seems we have those too...', { header: "Warning", sticky: true });
            }
          });
        }
        //e.preventDefault(); // Prevents any real link clicked inside the project to work
      })
      .hover(
        function() {
          // Don't highlight the active project
          if (activeProjectId != $(this).attr('id')) {

            hover($(this));
          }
        },
        function() {
          // Don't un-highlight the active project
          if (activeProjectId != $(this).attr('id')) {
            deactivate($(this));
          }
        }
      )
    ;
    
    //
    // The sparklines
    //
    $('#dashboard .sparklineBuilds').sparkline('html', {
      type: 'tristate',
      //posBarColor: '#46A546',
      //negBarColor: '#C43C35'
      posBarColor: 'rgb(124,196,0)',
      negBarColor: 'rgb(255,40,0)'
    });
    //
    // Tabs for the projects
    //
    $('.tabs').tabs();
    $('.tabs').bind('change', function (e) {
      $($(e.relatedTarget).attr('href')).hide(); // previous tab
      $($(e.target).attr('href')).fadeIn(300); // activated tab
    })
    //
    // Project log table sorting
    //
    $("#log table").tablesorter({ sortList: [[0,1]] }); // Sort the first column, DESC
  },
  
  initSectionHeader: function ()
  {
    $('#cintientLettering').fadeIn(500);
    $('.topbar').dropdown();
  },
  
  initSectionSettings: function ()
  {
    $('.tabs').tabs();
    $('.tabs').bind('change', function (e) {
      $($(e.relatedTarget).attr('href')).hide(); // previous tab
      $($(e.target).attr('href')).fadeIn(300); // activated tab
    })    
  },
  
  /**
   * This form initializer receives a top container (not necessarily a
   * form) and binds the closest() form's submit button to an AJAX call.
   * It searches for a given set of inputs within that top container and
   * sends them in the AJAX call.
   * 
   * Aditionally, it has a very specific behaviour where it iterates
   * through the provided formSelector parameter and groups it's input
   * field values by that formSelector id. This means that within a form
   * you can have groups of inputs that are neatly packed by their
   * group (formSelector) id. 
   */
  initGenericForm: function ()
  {
    var options = $.extend({
      formSelector : 'form',
      onSuccessRedirectUrl : null,
      onSuccessRedirectTimer : 3000, // milliseconds
      successMsg : 'Saved.'
    }, arguments[0] || {});
    
    //
    // Stop the default form submission behaviour by the submit button
    //
    $(options.formSelector).closest('form').submit(function() {
      return false;
    });
    // TODO: doubting this next find is the best implementation to get
    // the submit button of this form
    ($(options.formSelector).closest('form')).find(':submit').click(function (e) {
      $.ajax({
        url: options.submitUrl,
        data: function () {
          var data = {};
          //
          // Iterate through each of the existing (if more than one)
          // formSelector elements. Check the method's documentation
          // for more details.
          //
          $(options.formSelector).each(function () {
            var that = this;
            data[$(this).attr('id')] = function() {
              var x = {};
              $(':input', that).each( function() {
                x[this.name] = { type: this.type, value: this.value };
              });
              return x;
            }();
          });
          return data;
        }(),
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.success == null) {
            $.jGrowl('An unknown error occurred. Yeah, it seems we have those too... :-(', { header: "Warning", sticky: true });
          } else if (!data.success) {
            $.jGrowl(data.error + '', { header: "Warning", sticky: true });
          } else {
            $.jGrowl(options.successMsg);
            if (options.onSuccessRedirectUrl !== null) {
              setTimeout(function () {
                window.location.replace(options.onSuccessRedirectUrl);
              }, options.onSuccessRedirectTimer);
            }
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          $.jGrowl('An unknown error occurred. Yeah, it seems we have those too...', { header: "Warning", sticky: true });
          alert(errorThrown);
        }
      });
    });
    //
    // Show *all* the action buttons
    //
    /*
    setTimeout(function() {
      $(options.formSelector + ' input[type="submit"]').fadeIn(800);
      $(options.formSelector + ' button').each(function() {
        $(this).fadeIn(800);
      });
    }, 400);*/
  }
};