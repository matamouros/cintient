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
    //$('#dashboard a.projectLink').each( function() {
    $('#dashboard li.project').each( function() {
      $(this).click(function(e) {
        e.preventDefault();
        window.location = $(this).find('a.projectLink').attr('href');
      });
      $(this).hover(
        function() {
          $(this).css({
            "cursor" : "pointer",
            "background-color" : "rgb(248, 248, 248)"
          });
        },
        function() {
          $(this).css({
            "cursor" : "default",
            "background-color" : "#fff"
          });
        });
    });
    //
    // The sparklines
    //
    $('#dashboard .sparklineBuilds').sparkline('html', {
      type: 'tristate',
      posBarColor: '#46A546',
      negBarColor: '#C43C35'
    });
  },
  
  initSectionHeader: function ()
  {
    $('#cintientLettering').fadeIn(500);
    $('.topbar').dropdown();
  },
  
  /**
   * Sets up a page that was rigged with exclusive panes, so that only
   * one of them is shown at a time. Currently used for controlling
   * the project edit page and also the settings page. Note: exclusive
   * panes are closely tied to their respective activation links.
   * 
   * Activation links to panes should be inside an #exclusivePaneLinks
   * element. They must have a class name that is the same as the pane's
   * id.
   * 
   * Exclusive panes themselves should have an .exclusivePane class,
   * and their id must match the corresponding activation link's class.
   * 
   * A default pane object must be provided, in order to have a default
   * pane show up.
   */
  initExclusivePanes: function (defaultPane)
  {
    // Show the passed exclusivePane, hiding all others
    var activeExclusivePane = null;
    function showExclusivePane(exclusivePane) {
      if (activeExclusivePane === null || $(activeExclusivePane).attr('id') !== $(exclusivePane).attr('id')) {
        // Hide the previous pane
        $(activeExclusivePane).hide();
        // Reset the previous link
        $('#exclusivePaneLinks a.' + $(activeExclusivePane).attr('id')).css({
          "color" : "rgb(255,40,0)",
          "font-weight" : "bold",
          "text-decoration" : "none",
          "text-shadow" : "#303030 1px 1px 1px"
        });
        // Highlight the active link
        $('#exclusivePaneLinks a.' + $(exclusivePane).attr('id')).css({
          "color" : "rgb(255,60,0)",
          "text-shadow" : "0px 0px 6px rgba(255,40,0,1)",
          "text-decoration" : "none"
        });
        // Show the current pane
        exclusivePane.fadeIn(300);
        activeExclusivePane = exclusivePane;
      }
    }
    // Bind the click link events to their corresponding panes
    $('#exclusivePaneLinks a').bind('click', function() {
      showExclusivePane($('#paneContainer #' + $(this).attr('class')));
    });
    // Promptly show the default pane
    showExclusivePane($(defaultPane));
  },
  
  /**
   * 
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
    // Bind *only* to the submit button
    //
    $(options.formSelector).submit(function() {
      return false;
    });
    $(options.formSelector + ' :submit').click(function (e) {
      $.ajax({
        url: options.submitUrl,
        data: function () {
          var data = {};
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