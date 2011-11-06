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
  /** Enum for Bootstrap alert types */
  ALERT : {
    ERROR : 'error',
    INFO : 'info',
    SUCCESS : 'success',
    WARNING : 'warning'
  },
  
  addConfirmationToButton: function (e)
  {
    /*
    e.preventDefault();
    e.stopPropagation();
    var oldValue = $(this).prop('value');
    $(this).prop('value', 'Sure?');
    $(this).addClass('danger'); // Bootstrap .danger
    $(this).off('click');
    $(this).on('click', function () {
      
    });
    
    
    $('form').on('click', 'a.confirm, input.confirm, button.confirm', function () {
      e.preventDefault();
      e.stopPropagation();
      var oldValue = $(this).prop('value');
      $(this).prop('value', 'Sure?');
      $(this).addClass('danger'); // Bootstrap .danger
      $(this).off('click');
      $(this).on('click', function () {
        
      });
    });*/
  },
  
  /**
   * This is a small bare basics alert framework for handling alerts of
   * different severity levels. Higher severity levels have higher auto
   * dismiss timeouts, whereas errors are sticky, i.e., have to be
   * manually dismissed.
   * 
   * Usage (see "var options" for allowed parameters):
   * Cintient.alert({});
   */
  alert: function()
  {
    var options = $.extend({
      alertSelector : '#alertPane',
      autoDismissTimeout : 0,
      maxActiveAlerts : 3,
      message : 'Hello world!',
      type : this.ALERT.INFO,
    }, arguments[0] || {});
    //
    // DOM element creation (alert is now a reference to it)
    //
    var alert = $('<div class="alert-message fade in ' + options.type + '" style="display:none;"><a class="close" href="#">×</a>' + options.message + '</div>');
    //
    // Remove older alerts (yes, regardless of severity level)
    //
    if ($(options.alertSelector + ' .alert-message').length >= options.maxActiveAlerts) {
      $(options.alertSelector + ' .alert-message').first().slideUp(200);
      setTimeout(function() {
        $(options.alertSelector + ' .alert-message').first().remove();
      }, 200);
    }
    $(options.alertSelector).append(alert);
    $(alert).slideDown(100);
    $(options.alertSelector).alert(); // Currently only for providing close on the little x
    //
    // Errors are sticky, all others have growing auto dismiss timeouts
    //
    if (options.type == this.ALERT.SUCCESS) {
      options.autoDismissTimeout = 5000;
    } else if (options.type == this.ALERT.INFO) {
      options.autoDismissTimeout = 10000;
    } else if (options.type == this.ALERT.WARNING) {
      options.autoDismissTimeout = 30000;
    }
    if (options.autoDismissTimeout > 0) {
      setTimeout(function () {
        alert.slideUp(400);
        setTimeout(function() {
          alert.remove();
        }, 400);
      }, options.autoDismissTimeout);
    }
  },
  
  /**
   * Method for a vanilla unknown error alert
   */
  alertUnknown : function()
  {
    Cintient.alert({
      message : 'An unknown error occurred. Sorry about that...',
      type : Cintient.ALERT.ERROR
    });
  },
  
  /**
   * Method for a vanilla failed error alert
   */
  alertFailed : function(msg)
  {
    if (msg.length == 0) {
      msg = 'Failed!';
    }
    Cintient.alert({
      message : msg + '',
      type : Cintient.ALERT.INFO // Default normally failed as INFO severity
    });
  },
  
  /**
   * Method for a vanilla success error alert
   */
  alertSuccess : function(msg)
  {
    if (msg.length == 0) {
      msg = 'Success!';
    }
    Cintient.alert({
      message : msg + '',
      type : Cintient.ALERT.SUCCESS
    });
  },
  
  activateListItem : function (elem)
  {
    elem.css({
      "background-color" : "rgba(0, 67, 138, 0.1)" // Active color is stronger than hover
    });
  },
  
  deactivateListItem :function (elem)
  {
    elem.css({
      "cursor" : "default",
      "background-color" : "#fff",
    });
  },
  
  hoverListItem : function (elem)
  {
    elem.css({
      "cursor" : "pointer",
      "background-color" : "#eee"//"rgb(248, 248, 248)"
    });
  },
    
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
  
  initSectionAuthentication: function ()
  {
    $('#authentication form').fadeIn(300);
  },
  
  initSectionBuildHistory: function()
  {
    this._setupTabs();
  },
  
  initSectionDashboard: function ()
  {
    var options = $.extend({}, arguments[0] || {});
    var that = this;
    
    // Activate the default project
    this.activateListItem($('#dashboard li.project#' + activeProjectId));
    
    //
    // Stop propagation on a few special zones
    //
    $('#dashboard li.project a').click(function (e) {
      e.stopPropagation();
    });
    
    //
    // Hover & click on the project list
    //
    $('#dashboard li.project')
      .click(function(e) {
        //
        // Engage only if an unactive project was clicked
        //
        if ($(this).attr('id') != activeProjectId) {
          that.activateListItem($(this)); // Visual clues: promptly activate the clicked project
          that.deactivateListItem($('#dashboard li.project#' + activeProjectId)); // ... then deactivate the previously active project          
          activeProjectId = $(this).attr('id'); // Update the active project
          
          // Promptly hide the content
          // TODO: show a waiting indicator
          $('#dashboard #dashboardProject').hide();
          
          $.ajax({
            url: options.submitUrl,
            data: { pid : $(this).attr('id') },
            type: 'GET',
            cache: true,
            dataType: 'html',
            success: function(data, textStatus, XMLHttpRequest) {
              // Following condition according to jQuery's .load() method
              // documentation:
              // http://api.jquery.com/load/
              if (textStatus == 'success' || textStatus == 'notmodified') {
                var activeId = $('.tab-content .active').attr('id'); // Fetch the currently active id before it goes away
                $('#dashboard #dashboardProject').html(data); // Update the HTML (replace it)
                $('ul.tabs > li.active').removeClass('active'); // Throw away the HTML forced active tab
                $('.tab-content .active').removeClass('active'); // Throw away the HTML forced active content
                $('ul.tabs > li a[href="#' + activeId + '"]').parent().addClass('active'); // Honor the previously user active tab
                $('.tab-content #' + activeId).addClass('active'); // Honor the previously user active content
                $('.tabs').tabs(); // Init the Bootstrap tabs
                $("#log table").tablesorter({ sortList: [[0,1]] }); // Init the project log table sorter, sort the first column, DESC
                $('#dashboard #dashboardProject').fadeIn(300); // Show it all
              } else {
                alertUnknown();
              }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              alertUnknown();
            }
          });
        }
        //e.preventDefault(); // Prevents any real link clicked inside the project to work
      })
      .hover(
        function() {
          // Don't highlight the active project
          if (activeProjectId != $(this).attr('id')) {
            that.hoverListItem($(this));
          }
        },
        function() {
          // Don't un-highlight the active project
          if (activeProjectId != $(this).attr('id')) {
            that.deactivateListItem($(this));
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
    this._setupTabs();
    //
    // Project log table sorting
    //
    $("#log table").tablesorter({ sortList: [[0,1]] }); // Sort the first column, DESC
  },
  
  initSectionHeader: function ()
  {
    $('#cintientLettering').fadeIn(500);
    $('.topbar').dropdown();
    $('.tooltip').tipTip();
    $('#logoDropdown').hover(
      function () {
        $('#logoDropdown .dropdownArrow').css({ 'visibility' : 'visible' });
      },
      function () {
        $('#logoDropdown .dropdownArrow').css({ 'visibility' : 'hidden' });
      }
    );
  },
  
  initSectionProjectEdit: function ()
  {
    var options = $.extend({}, arguments[0] || {});
    
    this._setupTabs();
    
    /*
    =========================== Delete tab ============================ 
    */
    
    //
    // Toggle the project delete button on confirmation
    //
    $('#projectEdit #delete #pid').change(function () {
      if (this.checked) {
        $('#projectEdit #delete #deleteBtn')
          .prop('disabled', '')
          .removeClass('disabled');
      } else {
        $('#projectEdit #delete #deleteBtn')
          .prop('disabled', 'disabled')
          .addClass('disabled');
      }
    });
    
    /*
    ===================== Users tab - remove users ==================== 
    */
    $('#userList ul li .remove a').live('click', function(e) {
      e.preventDefault();
      $.ajax({
        url: $(this).attr('href'),
        // The username is recorded in the class attribute, as the first,
        // class - hence the split()
        data: { username: $(this).attr('class').split(' ')[0] },
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.success == null || !data.success) {
            Cintient.alertUnknown();
          } else {
            slideUpTime = 150;
            $('#userList ul li#' + data.username).slideUp(slideUpTime);
            setTimeout(
              function() {
                $('#userList ul li#' + data.username).remove();
              },
              slideUpTime
            );
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          Cintient.alertUnknown();
        }
      });
    });
    
    /*
    =================== Users tab - search/add users ================== 
    */
    
    //
    // Disable the form's default submit action, so that the tab's contents
    // don't go away when ENTER is pressed in the search input
    //
    $('#usersForm').submit(function() {
      return false;
    });

    timerId = null;
    userTermVal = null;
    searchUserPaneActive = false;
    $('#searchUserTextfield').keyup(function(e) {
      userTermVal = $(this).val();
      if (userTermVal.length > 1) {
        var triggerListRefresh = function()
        {
          $('#searchResultsPopover .content ul li').remove();
          $('#searchResultsPopover .content ul').append('<li class="spinningIcon"><img src="imgs/loading-3.gif" /></li>');
          $('#searchResultsPopover .popover').fadeIn(150);
          searchUserPaneActive = true;
          $.ajax({
            url: options.userSearchSubmitUrl,
            data: { userTerm: userTermVal },
            type: 'GET',
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
              $('#searchResultsPopover .content ul li').remove();
              if (data == null || data.success == null) {
                Cintient.alertUnknown();
                $('#searchResultsPopover .content ul').append('<li>Problems fetching users.</li>');
              } else if (!data.success) {
                $('#searchResultsPopover .content ul').append('<li>Problems fetching users.</li>');
              } else {
                if (data.result.length == 0) {
                  $('#searchResultsPopover .content ul').append('<li>No users found.</li>');
                } else {
                  found = 0
                  $('#searchResultsPopover .content ul').append('<li><span class="help-block">Click a user to add him to the project.</span></li>');
                  for (i = 0; i < data.result.length; i++) {
                    if ($('ul#userList li#' + data.result[i].username).length == 0) {
                      $('#searchResultsPopover .content ul').append('<li><a href="#" class="'+data.result[i].username+'"><img class="avatar40" src="'+data.result[i].avatar+'"/><h3>'+data.result[i].username+'</h3></a></li>');
                      found++;
                    }
                  };
                  if (found == 0) {
                    $('#searchResultsPopover .content ul').append('<li>No more users found.</li>');
                  }
                }
              }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              $('#searchResultsPopover .content ul li').remove();
              $('#searchResultsPopover .content ul').append('<li>Problems fetching users.</li>');
            }
          });
        };
        if (timerId !== null) {
          clearTimeout(timerId); // Clear previous timers on queue
        }
        if (e.which == 13) { // Imediatelly send request, if ENTER was depressed
          triggerListRefresh();
        } else {
          timerId = setTimeout(triggerListRefresh, 1000);
        }
      }
    });
    // Close the popover on click anywhere on the page
    $(document).click(function(){
      if (searchUserPaneActive) {
        $('#searchResultsPopover .popover').fadeOut(50);
        searchUserPaneActive = false;
      }
    });

    //
    // Add select widget user to the project list of users
    //
    $('#searchResultsPopover .content > ul li:not(:first-child)').live('click', function() {
      $.ajax({
        url: options.addUserSubmitUrl,
        data: { username: $('a', this).attr('class') },
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.success == null) {
            Cintient.alertUnknown();
          } else if (!data.success) {
            $('#userList > ul').append('<li>Problems adding user.</li>');
          } else {
            $('#userList > ul').append(data.html);
            $('#userList > ul li:last-child').slideDown(150);
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          Cintient.alertUnknown();
        }
      });
    });   
    
    /*
    ===================== Users tab - access level ==================== 
    */
    
    //
    // Bind the click link events to their corresponding panes
    //
    var activePopover = null;
    $('.actionItems .access a', $('#userList')).live('click', function(e) {
      if (activePopover != null) {
        activePopover.fadeOut(50);
      }
      // The username is recorded in the class attribute, as the first,
      // class - hence the split()
      var newActivePopover = $('#userList #accessLevelPaneLevels_' + $(this).attr('class').split(' ')[0]);
      if ($(activePopover).prop('id') != $(newActivePopover).prop('id')) {
        activePopover = newActivePopover;
        activePopover.fadeIn(50);
      } else {
        // No new popover is showing up, reset the flag
        activePopover = null;
      }
      e.stopPropagation();
    });
    // Close any menus on click anywhere on the page
    $(document).click( function(e){
      if ($(e.target).attr('class') != 'accessLevelPaneLevels' &&
          $(e.target).attr('class') != 'accessLevelPaneLevelsCheckbox' &&
          $(e.target).attr('class') != 'labelCheckbox' ) {
        if (e.isPropagationStopped()) { return; }
        if (activePopover != null) {
          activePopover.fadeOut(100);
          activePopover = null;
        }
      }
    });
    //
    // Setup auto save for access level pane changes
    //
    $('.accessLevelPopover .content input').live('click', function() {
      $.ajax({
        url: options.accessLevelPopoverSubmitUrl,
        data: { change: $(this).attr('value') },
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.success == null) {
            Cintient.alertUnknown();
          } else if (!data.success) {
            Cintient.alertFailed(data.error);
          } else {
            Cintient.alertSuccess(data.error);
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          Cintient.alertUnknown();
        }
      });
      $('.accessLevelPopover').fadeOut(300);
      activePopover = null;
    });
  },
  
  initSectionSettings: function ()
  {
    this._setupTabs();    
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
      type : 'POST',
      
      /**
       * We provide the following as attributes so that initGenericForm
       * calls can provide their own alert handlers.
       */
      cbUnknownResponse : function() {
        Cintient.alertUnknown();
      },
      
      cbFailedResponse : function(msg) {
        Cintient.alertFailed(msg);
      },
      
      cbSuccessResponse : function(msg) {
        Cintient.alertSuccess(msg);
      },
      
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
              // Applying these two filters is currently the best way I
              // can think of to leave out buttons
              $(':input', that).filter(':not(:submit)').filter(':not(:button)').each( function() {
                x[this.name] = { type: this.type, value: this.value };
              });
              return x;
            }();
          });
          return data;
        }(),
        type: options.type,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data == null || data.success == null) {
            options.cbUnknownResponse();
          } else if (!data.success) {
            options.cbFailedResponse(data.error);
          } else {
            options.cbSuccessResponse(data.error);
            if (options.onSuccessRedirectUrl !== null) {
              setTimeout(function () {
                window.location.replace(options.onSuccessRedirectUrl);
              }, options.onSuccessRedirectTimer);
            }
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          options.cbUnknownResponse();
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
  },
  
  _setupTabs: function()
  {
    //
    // Tabs for the projects
    //
    $('.tabs').tabs();
    $('.mainContent').on('change', '.tabs', function (e) {
      $($(e.relatedTarget).attr('href')).hide(); // previous tab
      //$($(e.target).attr('href')).fadeIn(300); // activated tab
      // This fadeIn() is directly patched into bootstrap-tabs.js, so
      // that we have fadeIn() behaviour from click 1.
    });
  }
};