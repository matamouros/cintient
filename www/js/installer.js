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
 * This is Cintient's installer JS helper class. It's pretty much event
 * oriented, so the only required thing is to set it up and call init().
 * 
 * @author Pedro Mata-Mouros <pedro.matamouros@gmail.com>, based on
 * initial work by voxmachina.
 */

var Installer = function (options)
{
  this.init(options);
};

Installer.prototype = {
  /**
   * 
   */
  init: function (options)
  {
    this.options = $.extend({
      /**
       * The current step index
       */
      curStepIndex : 0,

      /**
       * Keeps an eye on previously displayed steps
       */
      steps        : [],

      /**
       * Classes for selectors
       */
      elemsClasses : 
      {
        step       : 'installerStep'
      }
    }, arguments[0] || {});
    this._steps = $('.'+this.options.elemsClasses.step);
    this._run();
  },
  
  
  /**
   * Provide proper styling for on-the-fly created buttons
   */
  _styleButton : function(button)
  {
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
  },
  
  
  /**
   * Runs the installer handler. If it's the first time, a splash screen
   * is shown. If not, the proper installation step is displayed, although
   * currently this doesn't happen anymore.
   */
  _run : function ()
  {
    var that = this;
    // Show the splash screen
    if (this.options.curStepIndex == 0) {
      $('#splashHeader h1').fadeIn(300);
      $('#splashHeader img').fadeIn(300);
      setTimeout(
        function() {
          $('#splashHeader .greetings').fadeIn(1000);
        },
        1000
      );
      setTimeout(
        function() {
          $('#splashHeader .greetings').fadeOut(100);
          $('#splashHeader h1').fadeOut(500);
          $('#splashHeader img').fadeOut(500);
          setTimeout(
            function(){
              $('#splashHeader').hide();
              $('#logo').show(200);
              $('#mainMenu').fadeIn(500);
              that._displayStep();
            },
            700
          );
        },
        4000
      );
    } else {
      $('#logo').show(200);
      $('#mainMenu').fadeIn(500);
      this._displayStep();
    }
  },

  
  _displayStep : function ()
  {
    var step = this._steps[this.options.curStepIndex];
    
    // Refresh the section title
    $("#mainMenu #sectionName").text($(".stepTitle", step).text());
    
    // Empty out all previously drawn buttons
    $("#actionButtons").empty();
    
    // Register all inputs, only if it's the first time for this step
    if (this.options.steps.length < this.options.curStepIndex+1) {
      var that = this;
      $("li", step).each(function() {
        if ($(this).attr('class') == 'inputCheckOnChange') {
          that._registerCheckOnChangeInput($(this).attr('id'), $("input", this));
        }
      });
    } else {
      // Update the displayed steps
      this.options.steps[this.options.curStepIndex] = this.options.curStepIndex;
    }
    
    // Create the back button
    if (this.options.curStepIndex > 0) {
      var backBtn = document.createElement('button');
      this._styleButton(backBtn);
      $(backBtn).html('<div>&larr; Back</div>');
      backBtn.className = 'buttonText backButton';
      $(backBtn).bind('click', {self:this}, this._previousStepListener);
      $("#actionButtons").append(backBtn);
    }
    
    this._refreshProgressionButton();
    
    // Make it visible
    $(step).fadeIn(150);
  },
  
  
  _refreshProgressionButton: function()
  {
    // Empty out all progression buttons
    $("#actionButtons .progressionButton").remove();
    
    // Create the retry/next button
    var button = document.createElement('button');
    this._styleButton(button);
    button.className = 'buttonText progressionButton'; // TODO: browser safe?
    if (this._steps.length == this.options.curStepIndex+1) {
      $(button).html("<div>All set, let's go!</div>");
    } else {
      $(button).html('<div>Next &rarr;</div>');
    }
    if (this._isStepCleared()) {
      $(button).bind('click', {self:this}, this._nextStepListener);
    } else {
      $(button).hover(function(e) {
        $(this).css({
          "cursor" : "default",
          "border" : "2px solid #999",
          "box-shadow" : "none",
          "-webkit-box-shadow" : "none",
          "-moz-box-shadow" : "none"
        });
      });
      $(button).css({
        "cursor" : "default",
        "box-shadow" : "none",
        "-webkit-box-shadow" : "none",
        "-moz-box-shadow" : "none"
      });
      $(button).addClass('buttonTextInactive');
      $(button).click(function(e) {
        e.preventDefault();
      });
    }
    button.style.display = 'none';
    $("#actionButtons").append(button);
    setTimeout(function() {$(button).fadeIn(800)}, 400);
  },
  
  
  _isStepCleared: function ()
  {
    var step = this._steps[this.options.curStepIndex];
    return ($(step).find(".result.error").length === 0);
  },
  
  
  _updateInputValidation: function (result, ok, msg)
  {
    if (ok === true) {
      $(result).removeClass('error');
      $(result).addClass('success');
    } else {
      $(result).removeClass('success');
      $(result).addClass('error');
    }
    $(result).text(msg);
    this._refreshProgressionButton();
  },
  
  
  _registerCheckOnChangeInput : function (key, input)
  {
    var that = this;
    $(input).change(function () {
      var remoteCheck = true;
      var functionName = 'inputCheckOnChange' + key.charAt(0).toUpperCase() + key.slice(1);
      if (typeof window[functionName] === 'function') {
        var result = eval(functionName + '()');
        that._updateInputValidation($(this).parent().next('.result'), result.ok, result.msg);
        remoteCheck = !result.ok;
        // This is ugly, maybe do it better later on. If passwords don't
        // match, a remote check is triggered
        if ($(this).attr('type') == 'password') {
          return true;
        }
      }
      if (remoteCheck) {
        $.ajax({
          url: '?c=' + key + '&v=' + $(input).val(),
          type: 'GET',
          cache: false,
          dataType: 'json',
          success: function(data, textStatus, XMLHttpRequest) {
            if (typeof(data.ok) === "undefined") {
              // TODO: Error somewhere
              return false;
            } else {
              that._updateInputValidation($(input).parent().next('.result'), data.ok, data.msg);
              return true;
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            // TODO: Error
            alert(errorThrown);
          }
        });
        return false;
      }
    });
  },
  
  
  _previousStepListener : function (e)
  {
    e.preventDefault();
    var self = e.data.self;
    var curStep = self._steps[self.options.curStepIndex];
    // Hide this and show previous
    $(curStep).fadeOut(100, function () {
      self.options.curStepIndex--;
      // Update the breadcrumbs
      $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+2)).addClass('ghosted');
      $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+1)).removeClass('ghosted');
      self._displayStep();
    });
  },
  
  
  _nextStepListener : function (e)
  {
    e.preventDefault();
    var self = e.data.self;
    var curStep = self._steps[self.options.curStepIndex];
    self.options.curStepIndex++; // Increment the step
    if (self._steps.length != self.options.curStepIndex) {
      $(curStep).fadeOut(100, function() {
        // Update the breadcrumbs
        $('#mainMenu #historyBack .step-' + self.options.curStepIndex).addClass('ghosted');
        $('#mainMenu #historyBack .step-' + (self.options.curStepIndex+1)).removeClass('ghosted');
        self._displayStep();
      });
    } else {
      // Display a spinning waiting graphic and wait for a response
      // to the form submission
      $('#logo').fadeOut(100);
      $('#mainMenu').fadeOut(50);
      $('#actionButtons').fadeOut(50);
      $(curStep).fadeOut(100, function () {
        $('#done').html('<div id="pleaseWait">Please wait...</div><div><img src="www/imgs/loading-2.gif" /></div>');
        $('#done').fadeIn(500);
        setTimeout(function() {
          $('#logo').fadeIn(500);
        }, 1000);
      });
      self._formSubmit();
    }
  },
  
  
  _formSubmit: function ()
  {
    var data = function() {
      var x = {};
      $('.installerStep').find('input').each( function() {
        //x[this.name] = { type: this.type, value: this.value };
        x[this.name] = this.value;
      });
      $('.installerStep').find('textarea').each( function() {
        //x[this.name] = { type: this.type, value: this.value };
        x[this.name] = this.value;
      });
      return x;
    }();
    $.ajax({
      url: '?s=1',
      data: data,
      type: 'GET',
      cache: false,
      dataType: 'json',
      success: function(data, textStatus, XMLHttpRequest) {
        if (typeof data.ok === "undefined") {
          // TODO: Error somewhere
          return false;
        } else {
          var result = 'Failed.';
          if (data.ok === true) {
            result = 'Finished!';
          }
          $('#done').fadeOut(100, function () {
            $('#done').html('<div id="result">' + result + '</div><div id="resultMessage">' + data.msg + '</div>');
            $('#done').fadeIn(300);
          });
          return true;
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert(errorThrown);
      }
    });
  }
};
